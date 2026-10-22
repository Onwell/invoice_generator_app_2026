# invoice_generator_app_2026
by Onwell Masaraure

## Account setup

The login and registration forms use the MySQL database `mari_flow_db`. Create
that database in MySQL/phpMyAdmin before using the forms; the `users` table is
created automatically the first time someone logs in or registers.

For a default XAMPP installation, the app connects to `127.0.0.1:3306` as
`root` with an empty password. To use different connection details, set the
`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` environment
variables for PHP. The `DB_NAME` default is `mari_flow_db`.

New accounts receive the `user` role. To grant the first administrator, register
that account, then run this once in phpMyAdmin:

```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
```

Admins can promote or demote accounts from Admin → Users. The final admin role
cannot be removed, and an admin cannot demote their own account.

## Invoice PDF actions

On the Account page, save accepted payment methods and bank details under
**Invoice payment details**. They are included in invoice previews and generated
PDFs. Like invoice data, these settings are stored in that user's browser
storage, so they are available in the same browser profile.

**Email PDF** shares the PDF as an attachment through the device's native share
sheet when file sharing is supported. On browsers without file sharing, it
downloads an `.eml` email draft containing the recipient, subject, message, and
PDF attachment; open that draft with the computer's email app to send it.
Browsers do not permit websites to launch a local email app with an attachment
automatically, so the `.eml` fallback must be opened by the user.

**Print PDF** opens the generated PDF in a new browser tab, where it can be
printed or saved. These actions require the browser to allow downloads and
pop-ups for the app.
