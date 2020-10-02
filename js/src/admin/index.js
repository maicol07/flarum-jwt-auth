import app from 'flarum/app';

import JwtSettingsModal from './components/JwtSettingsModal';

app.initializers.add('maicol07-jwt-auth', () => {
  JwtSettingsModal()
});
