# PHP Cyber Uniproject

[English](README.md) | **ქართული**

[![CI](https://github.com/MrTabaOfficial/PHP-Cyber-Uniproject/actions/workflows/ci.yml/badge.svg)](https://github.com/MrTabaOfficial/PHP-Cyber-Uniproject/actions/workflows/ci.yml)
![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4)
![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Laravel-ზე დაწერილი აპლიკაცია ერთი თემის შესახებ: როგორ დავიცვათ მომხმარებლის ანგარიში. მასში რეალიზებულია ორფაქტორიანი ავთენტიფიკაცია, როლებზე დაფუძნებული წვდომა ადმინისტრატორის პანელზე, უსაფრთხოების მოვლენების აუდიტის ჟურნალი და ბრაუზერის მკაცრი უსაფრთხოების ჰედერები. თითოეული მათგანი დაფარულია ტესტებით.

პროექტი დაიწყო 2023 წლის დეკემბერში, როგორც საუნივერსიტეტო დავალება კიბერუსაფრთხოების კურსისთვის. 2026 წლის ოქტომბერში ის გადამუშავდა: Laravel 10-დან განახლდა Laravel 13-ზე, გასწორდა როლების გაფუჭებული ლოგიკა და დაემატა ადმინისტრატორის პანელი, აუდიტის ჟურნალი, ორფაქტორიანი ავთენტიფიკაციის სრული პროცესი, ჰედერები და ტესტები.

![მომხმარებლების სია ადმინისტრატორის პანელში](docs/screenshots/admin-users.png)

## შესაძლებლობები

| შესაძლებლობა | რას აკეთებს | სად ვნახოთ |
| --- | --- | --- |
| ორფაქტორიანი ავთენტიფიკაცია | TOTP კოდები ავთენტიფიკატორის აპლიკაციიდან. ჩართვა ძალაში შედის მხოლოდ კოდის დადასტურების შემდეგ. რვა ერთჯერადი აღდგენის კოდი ნაჩვენებია მხოლოდ ერთხელ. | [two-factor.blade.php](resources/views/profile/two-factor.blade.php), [config/fortify.php](config/fortify.php) |
| სავალდებულო ორფაქტორიანი ავთენტიფიკაცია ადმინისტრატორებისთვის | ადმინისტრატორი, რომელსაც ის ჩართული არ აქვს, ადმინისტრატორის პანელის ნაცვლად გადადის გამართვის გვერდზე. | [EnsureTwoFactorEnabled.php](app/Http/Middleware/EnsureTwoFactorEnabled.php) |
| როლებზე დაფუძნებული წვდომა | enum-ზე დაფუძნებული `role` სვეტი, policy და middleware. როლის მინიჭება mass assignment-ით შეუძლებელია. | [Role.php](app/Enums/Role.php), [UserPolicy.php](app/Policies/UserPolicy.php), [EnsureUserIsAdmin.php](app/Http/Middleware/EnsureUserIsAdmin.php) |
| ადმინისტრატორის პანელი | მომხმარებლების ძებნა და გაფილტვრა, სახელის, ელფოსტისა და როლის შეცვლა, მომხმარებლის წაშლა, თითოეული ანგარიშის ბოლო აქტივობის ნახვა. | [UserController.php](app/Http/Controllers/Admin/UserController.php) |
| უსაფრთხოების აუდიტის ჟურნალი | იწერს შესვლებს, წარუმატებელ მცდელობებს, დაბლოკვებს, გასვლებს, პაროლისა და ორფაქტორიანი ავთენტიფიკაციის ცვლილებებს და ადმინისტრატორის მოქმედებებს IP მისამართთან და ბრაუზერთან ერთად. | [RecordSecurityEvents.php](app/Listeners/RecordSecurityEvents.php), [SecurityEvent.php](app/Models/SecurityEvent.php) |
| უსაფრთხოების ჰედერები | Content Security Policy, ფრეიმში ჩასმის აკრძალვა, `nosniff`, referrer და permissions პოლიტიკები, HSTS HTTPS-ზე, `no-store` ავტორიზებული გვერდებისთვის. | [SecurityHeaders.php](app/Http/Middleware/SecurityHeaders.php) |
| პაროლის პოლიტიკა | მინიმუმ 12 სიმბოლო: დიდი და პატარა ასოები, ციფრი და სიმბოლო. production გარემოში მოწმდება გაჟონილი პაროლების ბაზაში. | [AppServiceProvider.php](app/Providers/AppServiceProvider.php) |
| მოთხოვნების ლიმიტი | წუთში 5 შესვლის მცდელობა ერთ ელფოსტასა და IP მისამართზე, წუთში 5 ორფაქტორიანი მცდელობა, წუთში 6 მოთხოვნა რეგისტრაციასა და პაროლის აღდგენაზე. | [FortifyServiceProvider.php](app/Providers/FortifyServiceProvider.php), [routes/auth.php](routes/auth.php) |
| ელფოსტის დადასტურება | მთავარი გვერდი (dashboard) და ადმინისტრატორის პანელი მოითხოვს დადასტურებულ ელფოსტას. | [routes/web.php](routes/web.php) |

## ეკრანის სურათები

| ორფაქტორიანი ავთენტიფიკაციის გამართვა | აღდგენის კოდები |
| --- | --- |
| ![გამართვა QR კოდით](docs/screenshots/two-factor-setup.png) | ![გამართვის შემდეგ ნაჩვენები აღდგენის კოდები](docs/screenshots/two-factor-enabled.png) |

| კოდის მოთხოვნა შესვლისას | აუდიტის ჟურნალი |
| --- | --- |
| ![ორფაქტორიანი კოდის მოთხოვნა შესვლისას](docs/screenshots/two-factor-challenge.png) | ![აუდიტის ჟურნალი ადმინისტრატორებისთვის](docs/screenshots/audit-log.png) |

| მთავარი გვერდი | მომხმარებლის რედაქტირება |
| --- | --- |
| ![მთავარი გვერდი ანგარიშის უსაფრთხოების სტატუსით](docs/screenshots/dashboard.png) | ![მომხმარებლის რედაქტირების გვერდი](docs/screenshots/admin-user-edit.png) |

სურათებზე ნაჩვენები საიდუმლო გასაღები და აღდგენის კოდები ეკუთვნის ლოკალურ სადემონსტრაციო ბაზას, რომელიც აღარ არსებობს.

## საფრთხეები და დაცვის მექანიზმები

| საფრთხე | დაცვის მექანიზმი | ტესტი |
| --- | --- | --- |
| პაროლის გამოცნობა | ლიმიტი ელფოსტასა და IP მისამართზე. ყველა წარუმატებელი მცდელობა და დაბლოკვა იწერება ჟურნალში. | [AuditLogTest](tests/Feature/Security/AuditLogTest.php) |
| მოპარული პაროლი | პაროლის შემდეგ მოითხოვება ორფაქტორიანი კოდი. სესია ავტორიზებული არ არის, სანამ კოდი არ დადასტურდება. | [TwoFactorAuthenticationTest](tests/Feature/Security/TwoFactorAuthenticationTest.php) |
| სუსტი პაროლები | ერთიანი პაროლის პოლიტიკა რეგისტრაციის, შეცვლისა და აღდგენისთვის. | [PasswordPolicyTest](tests/Feature/Security/PasswordPolicyTest.php) |
| პრივილეგიების მითვისება | `role` გამორიცხულია mass assignment-იდან. ადმინისტრატორის მარშრუტებს სჭირდება admin middleware და policy-ის შემოწმება. საკუთარ როლს ვერავინ შეცვლის. | [RoleTest](tests/Feature/RoleTest.php), [UserManagementTest](tests/Feature/Admin/UserManagementTest.php) |
| ბოლო ადმინისტრატორის დაკარგვა | ადმინისტრატორი პანელიდან ვერ წაშლის საკუთარ თავს და ვერ ჩამოიქვეითებს როლს. ერთადერთი ადმინისტრატორი ვერ წაშლის საკუთარ ანგარიშს. | [UserManagementTest](tests/Feature/Admin/UserManagementTest.php) |
| Cross-site scripting (XSS) | Blade ახდენს გამოტანილი ტექსტის escape-ს. Content Security Policy ბლოკავს inline სკრიპტებსა და inline event handler-ებს, და გვერდებში ისინი არ არის. | [SecurityHeadersTest](tests/Feature/Security/SecurityHeadersTest.php) |
| Clickjacking | `X-Frame-Options: DENY` და `frame-ancestors 'none'`. | [SecurityHeadersTest](tests/Feature/Security/SecurityHeadersTest.php) |
| Cross-site request forgery (CSRF) | Laravel-ის CSRF ტოკენი ყველა ფორმაზე, რომელიც მონაცემებს ცვლის, და `form-action 'self'`. | ფრეიმვორკის ნაგულისხმევი ქცევა |
| გასვლის შემდეგ გვერდის აღდგენა ქეშიდან | `Cache-Control: no-store` ავტორიზებული მომხმარებლის პასუხებზე. | [SecurityHeadersTest](tests/Feature/Security/SecurityHeadersTest.php) |
| შეუმჩნეველი ანგარიშის მითვისება | მომხმარებელი მთავარ გვერდზე ხედავს საკუთარ ბოლო უსაფრთხოების მოვლენებს. ადმინისტრატორი ხედავს ყველა მოვლენას. მოვლენები რჩება ანგარიშის წაშლის შემდეგაც. | [AuditLogTest](tests/Feature/Security/AuditLogTest.php) |
| ორფაქტორიანი ავთენტიფიკაციის შეცვლა უყურადღებოდ დატოვებული სესიიდან | მისი პარამეტრების გვერდი და ყველა მოქმედება მოითხოვს ახლახან დადასტურებულ პაროლს. | [TwoFactorAuthenticationTest](tests/Feature/Security/TwoFactorAuthenticationTest.php) |

## ლოკალურად გაშვება

მოთხოვნები: PHP 8.3 ან უფრო ახალი `pdo_sqlite`, `mbstring`, `openssl` და `fileinfo` გაფართოებებით, Composer 2 და Node.js 20 ან უფრო ახალი.

1. დააინსტალირეთ დამოკიდებულებები.

   ```bash
   composer install
   npm install
   ```

2. შექმენით `.env`, SQLite ბაზა და სადემონსტრაციო ანგარიშები.

   ```bash
   composer setup
   ```

3. ააწყვეთ ფრონტენდის ფაილები და გაუშვით სერვერი.

   ```bash
   npm run build
   php artisan serve
   ```

4. გახსენით <http://localhost:8000>.

`composer setup` უშვებს `migrate:fresh --seed` ბრძანებას, ამიტომ ყოველ გაშვებაზე შლის ლოკალურ ბაზას.

### სადემონსტრაციო ანგარიშები

| როლი | ელფოსტა | პაროლი |
| --- | --- | --- |
| ადმინისტრატორი | `admin@example.com` | `Demo-Passw0rd!` |
| მომხმარებელი | `user@example.com` | `Demo-Passw0rd!` |

seeder ამ ანგარიშებს არ ქმნის, როცა `APP_ENV=production`.

სრული პროცესის სანახავად შედით ადმინისტრატორის ანგარიშით და გახსენით **Users**. აპლიკაცია ხელახლა მოითხოვს პაროლს და შემდეგ გაგატარებთ ორფაქტორიანი ავთენტიფიკაციის გამართვას ნებისმიერი ავთენტიფიკატორის აპლიკაციით.

დადასტურებისა და პაროლის აღდგენის წერილები არ იგზავნება. `MAIL_MAILER=log` პარამეტრით ისინი იწერება ფაილში `storage/logs/laravel.log`.

## ტესტები

```bash
composer test   # 80 feature ტესტი SQLite-ზე ოპერატიულ მეხსიერებაში
composer lint   # კოდის სტილის შემოწმება Laravel Pint-ით
```

ორფაქტორიანი ავთენტიფიკაციის ტესტები პროვაიდერის mock-ის ნაცვლად შენახული საიდუმლო გასაღებიდან ნამდვილ TOTP კოდებს აგენერირებს. იგივე პროცესი გაეშვა headless Chrome-შიც, ჩართული Content Security Policy-ით, და კონსოლში შეცდომა არ დაფიქსირებულა.

GitHub Actions ყოველ push-სა და pull request-ზე უშვებს ტესტებს, სტილის შემოწმებას, ფრონტენდის აწყობას, `composer audit`-სა და `npm audit`-ს. იხილეთ [ci.yml](.github/workflows/ci.yml).

## პროექტის სტრუქტურა

ნაჩვენებია მხოლოდ ამ პროექტისთვის დამახასიათებელი ნაწილები. დანარჩენი Laravel-ის სტანდარტული სტრუქტურაა.

```text
PHP-Cyber-Uniproject/
├── .github/workflows/ci.yml            # აწყობა, სტილის შემოწმება, ტესტები, დამოკიდებულებების აუდიტი
├── app/
│   ├── Enums/
│   │   ├── Role.php                    # user / admin
│   │   └── SecurityEventType.php       # აუდიტის ჟურნალის ყველა მოვლენა, სახელითა და ფერით
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AuditLogController.php
│   │   │   │   └── UserController.php  # სია, რედაქტირება, როლის შეცვლა, წაშლა
│   │   │   ├── Auth/                   # რეგისტრაცია, ელფოსტის დადასტურება, პაროლის აღდგენა
│   │   │   ├── DashboardController.php
│   │   │   ├── ProfileController.php
│   │   │   └── TwoFactorSettingsController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureTwoFactorEnabled.php  # ადმინისტრატორს სავალდებულოდ სჭირდება ორფაქტორიანი ავთენტიფიკაცია
│   │   │   ├── EnsureUserIsAdmin.php
│   │   │   └── SecurityHeaders.php     # CSP, HSTS, ფრეიმის აკრძალვა, no-store
│   │   └── Requests/Admin/UpdateUserRequest.php
│   ├── Listeners/RecordSecurityEvents.php  # ავთენტიფიკაციის მოვლენები -> აუდიტის ჟურნალი
│   ├── Models/
│   │   ├── SecurityEvent.php
│   │   └── User.php
│   ├── Policies/UserPolicy.php
│   └── Providers/
│       ├── AppServiceProvider.php      # პაროლის პოლიტიკა
│       └── FortifyServiceProvider.php  # შესვლისა და ორფაქტორიანი კოდის ლიმიტები
├── config/
│   ├── fortify.php                     # ორფაქტორიანი ავთენტიფიკაცია დადასტურებით
│   └── security.php                    # ადმინისტრატორის წესი, ჟურნალის შენახვის ვადა
├── database/
│   ├── factories/UserFactory.php       # admin() და withTwoFactor() მდგომარეობები
│   ├── migrations/                     # users, role, ორფაქტორიანი სვეტები, security_events
│   └── seeders/DatabaseSeeder.php      # სადემონსტრაციო ანგარიშები
├── docs/screenshots/
├── resources/
│   ├── css/app.css                     # Tailwind CSS 4-ის კონფიგურაცია
│   ├── js/app.js                       # Alpine.js
│   └── views/
│       ├── admin/                      # მომხმარებლებისა და აუდიტის ჟურნალის გვერდები
│       ├── auth/                       # შესვლა, რეგისტრაცია, ორფაქტორიანი კოდის მოთხოვნა
│       ├── components/                 # ღილაკები, ბეჯები, უსაფრთხოების მოვლენების ცხრილი
│       ├── profile/                    # პროფილი და ორფაქტორიანი ავთენტიფიკაციის პარამეტრები
│       ├── dashboard.blade.php
│       └── welcome.blade.php
├── routes/
│   ├── auth.php                        # რეგისტრაცია, ელფოსტის დადასტურება, პაროლის აღდგენა
│   └── web.php                         # მთავარი გვერდი, პროფილი, ადმინისტრატორის პანელი
└── tests/Feature/
    ├── Admin/UserManagementTest.php
    ├── Auth/                           # შესვლა, რეგისტრაცია, აღდგენა, დადასტურება
    ├── Security/
    │   ├── AuditLogTest.php
    │   ├── PasswordPolicyTest.php
    │   ├── SecurityHeadersTest.php
    │   └── TwoFactorAuthenticationTest.php
    ├── ProfileTest.php
    └── RoleTest.php
```

## კონფიგურაცია

| ცვლადი | ნაგულისხმევი | მნიშვნელობა |
| --- | --- | --- |
| `SECURITY_ADMIN_REQUIRES_2FA` | `true` | ადმინისტრატორმა პანელის გამოყენებამდე უნდა ჩართოს ორფაქტორიანი ავთენტიფიკაცია. |
| `SECURITY_AUDIT_RETENTION_DAYS` | `90` | რამდენი დღის შემდეგ შლის ყოველდღიური `model:prune` უსაფრთხოების მოვლენას. |
| `SESSION_SECURE_COOKIE` | `false` | დააყენეთ `true`, როცა საიტი HTTPS-ით მუშაობს. |

## ცნობილი შეზღუდვები

- Content Security Policy სკრიპტებისთვის უშვებს `'unsafe-eval'`-ს. Alpine.js-ს ის სჭირდება inline გამოსახულებების შესასრულებლად. inline სკრიპტები კვლავ დაბლოკილია.
- ლიმიტი მოქმედებს ელფოსტასა და IP მისამართზე. თავდამსხმელი, რომელიც IP მისამართებს ცვლის, იზღუდება მხოლოდ თითოეულ მისამართზე. CAPTCHA არ არის.
- აუდიტის ჟურნალი ჩვეულებრივი ცხრილია ბაზაში. ბაზაზე წვდომის მქონე თავდამსხმელს შეუძლია მისი შეცვლა.
- ძველი ჩანაწერების წასაშლელად საჭიროა გაშვებული Laravel scheduler (`php artisan schedule:work` ან cron ჩანაწერი).
- აპლიკაცია არ არის განთავსებული სერვერზე და დატვირთვის ტესტი არ გაუვლია. ეს სასწავლო პროექტია და არა production სერვისი.

## ლიცენზია

გამოქვეყნებულია [MIT ლიცენზიით](LICENSE).
