# LibingGuide Project Structure

```
LibingGuide/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── DetectGravesFromTiles.php
│   │       ├── SendBurialReminders.php
│   │       └── SendInstallmentReminders.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── ConfirmablePasswordController.php
│   │   │   │   ├── EmailVerificationNotificationController.php
│   │   │   │   ├── EmailVerificationPromptController.php
│   │   │   │   ├── NewPasswordController.php
│   │   │   │   ├── PasswordController.php
│   │   │   │   ├── PasswordResetLinkController.php
│   │   │   │   ├── RegisteredUserController.php
│   │   │   │   └── VerifyEmailController.php
│   │   │   ├── ActivityLogController.php
│   │   │   ├── AmenityController.php
│   │   │   ├── BurialController.php
│   │   │   ├── ClientController.php
│   │   │   ├── ColumbariumSectionController.php
│   │   │   ├── ColumbaryNicheController.php
│   │   │   ├── ContractController.php
│   │   │   ├── Controller.php
│   │   │   ├── DashboardController.php
│   │   │   ├── InquiryController.php
│   │   │   ├── LotTypeController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── PlotController.php
│   │   │   ├── PreNeedPlanController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── PublicBookingController.php
│   │   │   └── PublicSearchController.php
│   │   ├── Middleware/
│   │   │   └── Role.php
│   │   └── Requests/
│   │       ├── Auth/
│   │       │   └── LoginRequest.php
│   │       ├── ProfileUpdateRequest.php
│   │       └── StorePlotRequest.php
│   ├── Models/
│   │   ├── ActivityLog.php
│   │   ├── Amenity.php
│   │   ├── Burial.php
│   │   ├── Client.php
│   │   ├── ColumbariumSection.php
│   │   ├── ColumbaryNiche.php
│   │   ├── Contract.php
│   │   ├── Inquiry.php
│   │   ├── InstallmentSchedule.php
│   │   ├── LotType.php
│   │   ├── Notification.php
│   │   ├── Payment.php
│   │   ├── Plot.php
│   │   ├── PreNeedPlan.php
│   │   └── User.php
│   ├── Observers/
│   │   ├── BurialObserver.php
│   │   ├── ContractObserver.php
│   │   ├── PaymentObserver.php
│   │   └── PlotObserver.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── View/
│       └── Components/
│           ├── AppLayout.php
│           └── GuestLayout.php
├── bootstrap/
│   ├── cache/
│   │   ├── .gitignore
│   │   ├── packages.php
│   │   └── services.php
│   ├── app.php
│   └── providers.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   └── session.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_06_05_000001_create_plots_table.php
│   │   ├── 2026_06_05_000002_create_clients_table.php
│   │   ├── 2026_06_05_000003_create_contracts_table.php
│   │   ├── 2026_06_05_000004_create_payments_table.php
│   │   ├── 2026_06_05_000005_create_installment_schedules_table.php
│   │   ├── 2026_06_05_000006_create_burials_table.php
│   │   ├── 2026_06_05_000007_create_activity_logs_table.php
│   │   ├── 2026_06_05_000008_create_notifications_table.php
│   │   ├── 2026_06_06_000001_create_inquiries_table.php
│   │   ├── 2026_06_06_000002_create_pre_need_plans_table.php
│   │   ├── 2026_06_06_000003_create_columbary_niches_table.php
│   │   ├── 2026_06_06_000004_add_role_to_users_table.php
│   │   ├── 2026_06_06_000005_add_pre_need_plan_id_to_contracts_table.php
│   │   ├── 2026_06_13_121149_create_lot_types_table.php
│   │   ├── 2026_06_13_121150_create_amenities_table.php
│   │   └── 2026_06_13_121150_create_columbarium_sections_table.php
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   └── SiteContentSeeder.php
│   ├── .gitignore
│   └── database.sqlite
├── docker/
│   ├── entrypoint.sh
│   ├── nginx.conf
│   ├── php.ini
│   └── supervisord.conf
├── public/
│   ├── build/
│   │   ├── assets/
│   │   │   ├── app-Ct-oiUSe.css
│   │   │   └── app-DO2nEFzp.js
│   │   └── manifest.json
│   ├── images/
│   │   ├── heritage-logo.png
│   │   └── satellite-tile.jpg
│   ├── js/
│   │   └── map.js
│   ├── tiles/
│   │   └── 20/
│   │       ├── 877277/
│   │       │   ├── 475471.png .. 475478.png
│   │       │   ... (8 subdirectories with .png tiles)
│   │       └── 877286/
│   │           ├── 475471.png .. 475478.png
│   ├── .htaccess
│   ├── favicon.ico
│   ├── index.php
│   └── robots.txt
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   └── app.js
│   └── views/
│       ├── activity_logs/
│       │   └── index.blade.php
│       ├── amenities/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── auth/
│       │   ├── confirm-password.blade.php
│       │   ├── forgot-password.blade.php
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   ├── reset-password.blade.php
│       │   └── verify-email.blade.php
│       ├── burials/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── clients/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── columbarium-sections/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── columbary-niches/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── components/
│       │   ├── application-logo.blade.php
│       │   ├── auth-session-status.blade.php
│       │   ├── danger-button.blade.php
│       │   ├── dropdown-link.blade.php
│       │   ├── dropdown.blade.php
│       │   ├── input-error.blade.php
│       │   ├── input-label.blade.php
│       │   ├── modal.blade.php
│       │   ├── nav-link.blade.php
│       │   ├── primary-button.blade.php
│       │   ├── responsive-nav-link.blade.php
│       │   ├── secondary-button.blade.php
│       │   └── text-input.blade.php
│       ├── contracts/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   ├── pdf.blade.php
│       │   └── show.blade.php
│       ├── inquiries/
│       │   ├── create.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── layouts/
│       │   ├── app.blade.php
│       │   ├── guest.blade.php
│       │   ├── navigation.blade.php
│       │   └── public.blade.php
│       ├── lot-types/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── notifications/
│       │   └── index.blade.php
│       ├── partials/
│       │   ├── public-footer.blade.php
│       │   └── public-nav.blade.php
│       ├── payments/
│       │   ├── create.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── plots/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── pre-need-plans/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── profile/
│       │   ├── partials/
│       │   │   ├── delete-user-form.blade.php
│       │   │   ├── update-password-form.blade.php
│       │   │   └── update-profile-information-form.blade.php
│       │   └── edit.blade.php
│       ├── public/
│       │   ├── columbarium.blade.php
│       │   ├── confirmation.blade.php
│       │   ├── find.blade.php
│       │   ├── inquire.blade.php
│       │   ├── lots.blade.php
│       │   ├── plan-detail.blade.php
│       │   ├── plans.blade.php
│       │   └── reserve-form.blade.php
│       ├── dashboard.blade.php
│       └── welcome.blade.php
├── routes/
│   ├── api.php
│   ├── auth.php
│   ├── console.php
│   └── web.php
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   │   ├── AuthenticationTest.php
│   │   │   ├── EmailVerificationTest.php
│   │   │   ├── PasswordConfirmationTest.php
│   │   │   ├── PasswordResetTest.php
│   │   │   ├── PasswordUpdateTest.php
│   │   │   └── RegistrationTest.php
│   │   ├── ExampleTest.php
│   │   └── ProfileTest.php
│   ├── Unit/
│   │   └── ExampleTest.php
│   └── TestCase.php
├── .dockerignore
├── .editorconfig
├── .env
├── .env.example
├── .gitattributes
├── .gitignore
├── .npmrc
├── artisan
├── burial-locator-laravel-plan.md
├── composer.json
├── composer.lock
├── CONCEPT_PAPER.md
├── docker-compose.yml
├── Dockerfile
├── FILE_STRUCTURE.md
├── memorial-map-full-plan.docx
├── memorial-map-offline-map-plan.md
├── package-lock.json
├── package.json
├── phpunit.xml
├── postcss.config.js
├── README.md
├── script.md
├── SYSTEM_ARCHITECTURE.md
├── tailwind.config.js
├── vite.config.js
└── TODO.md
```
