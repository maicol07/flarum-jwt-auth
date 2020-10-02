import { settings } from '@fof-components';

const { SettingsModal, items: { StringItem } } = settings;

export default () => {
  app.extensionSettings['maicol07-jwt-auth'] = () => app.modal.show(
      new SettingsModal({
        title: app.translator.trans('maicol07-jwt-auth.admin.settings.title'),
        size: 'small',
        items: [
          <StringItem key="maicol07-jwt-auth.iss">
            {app.translator.trans('maicol07-jwt-auth.admin.settings.iss')}
          </StringItem>
        ],
      }),
  );
};