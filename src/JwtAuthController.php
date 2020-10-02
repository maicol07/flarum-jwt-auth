<?php

namespace Maicol07\JwtAuth;

use Exception;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Flarum\Api\Controller\CreateUserController;
use Flarum\User\Exception\PermissionDeniedException;
use Flarum\User\UserRepository;
use Flarum\Api\Client;
use Flarum\Http\Rememberer;
use Flarum\Http\SessionAuthenticator;
use Flarum\Foundation\Application;
use RuntimeException;

class JwtAuthController implements RequestHandlerInterface
{
    /** @var UserRepository */
    protected $users;

    /** @var Client */
    protected $api;

    /** @var SessionAuthenticator */
    protected $authenticator;

    /** @var Rememberer */
    protected $rememberer;

    /** @var string */
    protected $path;

    /** @var string */
    protected $public_path;

    /** @var string */
    protected $site_url;
    
    /** @var string */
    protected $iss;
    
    /**
     * @param Client $api
     * @param SessionAuthenticator $authenticator
     * @param Rememberer $rememberer
     * @param UserRepository $users
     * @param Application $app
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(
        Client $api,
        SessionAuthenticator $authenticator,
        Rememberer $rememberer,
        UserRepository $users,
        Application $app,
        SettingsRepositoryInterface $settings
    ) {
        $this->api = $api;
        $this->authenticator = $authenticator;
        $this->rememberer = $rememberer;
        $this->users = $users;
        $this->path = $app->storagePath();
        $this->public_path = $app->publicPath();
        $conf = app('flarum.config');
        $this->site_url = $conf['url'];
        $this->iss = $settings->get('maicol07-jwt-auth.iss');
    }
    
    private function login(User $user, string $avatar, Request $request, ?ResponseInterface $response=null): void
    {
        // Login
        $id = $user->id;
    
        $user->changeAvatarPath($avatar);
        $user->save();
    
        $session = $request->getAttribute('session');
        $this->authenticator->logIn($session, $id);
        
        if ($response !== null) {
            $this->rememberer->rememberUser($response, $id);
        }
    }
    
    /**
     * @param Request $request
     * @return ResponseInterface
     * @throws PermissionDeniedException
     */
    public function handle(Request $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $token = (new Parser())->parse((string) $queryParams['token']);

        $jwt_user = $token->getClaim('user');
        
        // remove any sizing params
        $avatar = Arr::get($jwt_user, 'attributes.avatarUrl');
        $param = '?sz=';
        if (strpos($avatar, $param)) {
            $avatar = substr($avatar, 0, strpos($avatar, $param));
        }
        
        $user = $this->users->findByIdentification(Arr::get($jwt_user, 'attributes.email') ?? Arr::get($jwt_user, 'attributes.username'));

        if ($user !== null) {
            $this->login($user, $avatar, $request);
        } else {
            // Signup
            $jti = $token->getClaim('jti');

            $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
            $data->setIssuer($this->iss);
            $data->setAudience($this->site_url);
            $data->setId($jti);

            if (!$token->validate($data)) {
                if ($token->isExpired()) {
                    $message = 'Token expired.';
                }
    
                throw new PermissionDeniedException($message ?? 'Invalid token.');
            }
            
            $jwt_user['attributes']['isEmailConfirmed'] = true;
    
            $controller = CreateUserController::class;
    
            $actor = $this->users->findOrFail(1);
            $body = ['data' => $jwt_user];
    
            try {
                $response = $this->api->send($controller, $actor, [], $body);
            } catch (Exception $e) {
                throw new RuntimeException("Error during signup");
            }
    
            $body = json_decode($response->getBody(), false);
            if (isset($body->data)) {
                $id = $body->data->id;
                $user = $this->users->findOrFail($id);
                $user->activate();
                $user->save();

                $this->login($user, $avatar, $request);
            }
        }
    
        return new RedirectResponse('/');
    }
}
