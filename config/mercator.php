<?php

return [
    'cert' => [
        'mail-from' => 'mercator@localhost',
        'mail-to' => 'helpdesk@localhost',
        'mail-subject' => '[Mercator] Certificate expiration',
        'check-frequency' => '0',
        'expire-delay' => '1',
        'group' => '0',
        'repeat-notification' => '0',
    ],
    'cve' => [
        'mail-from' => 'mercator@localhost',
        'mail-to' => 'secops@localhost',
        'mail-subject' => '[Mercator] Vulnerability detected',
        'check-frequency' => '1',
        'provider' => 'https://vulnerability.circl.lu',
        'guesser' => 'https://cpe-guesser.cve-search.org',
    ],
    'parameters' => [
        'security_need_auth' => false,
        'application_documents' => false,
        'max_nodes' => 500,
    ],
    'cpe' => [
        'guesser' => 'https://cpe-guesser.cve-search.org',
    ],
    'cartography' => [
        'reminders_enabled' => false,
        'reminder_from' => 'mercator@localhost.com',
        'reminder_to' => 'mercator@localhost.com',
        'reminder_subject' => '[Mercator] Rappel',
        'reminder_body' => '<!DOCTYPE html>
<html lang=\'fr\'>
<body>
  <p>Bonjour :name,</p>
  <p>
    :count objet(s) dont tu es le cartographe n\'ont pas été mis à jour depuis plus de :months mois :
  </p>
  :list
  <p>
    Merci de mettre à jour ces objets dans <a href=\'https://www.mercator.localhost\'>Mercator</a>.
  </p>
  <p><em>Ce message a été généré automatiquement par Mercator. Merci de ne pas y répondre directement.<br>
Si tu penses avoir reçu ce message par erreur, contacte ton administrateur.</em></p>
</body>
</html>',
        'reminder_months' => 1,
        'reminder_every_days' => 30,
        'notifier_enabled' => false,
        'notifier_to' => 'mercator@localhost.com',
        'notifier_from' => 'mercator@localhost.com',
        'notifier_subject' => '[Mercator] Objet :name modifié',
        'notifier_body' => '<!DOCTYPE html>
<html lang=\'fr\'>
<body>
  <p>L\'objet <a href=":object_url">:object</a> a été <a href=":object_history_url">modifié</a> par :user :email.</p>
</body>
</html>',
        'reminder_last_sent' => null,
    ],
];
