# Laracap

Laracap is an open-source, self-hosted web management panel designed specifically for **Capacitor apps**. Built on top of Laravel 12 and Filament v4, this platform acts as a powerful alternative to commercial services like Ionic Appflow or Expo EAS by allowing you to completely own your over-the-air (OTA) update infrastructure.

## 🚀 Features

The system is constructed with a scalable Multi-Tenant architecture:

- **Application Management**: Safely register and manage multiple standalone Capacitor applications within a single dashboard. Data is strictly isolated by User ID.
- **Channel Routing**: Deploy updates to specific release streams (e.g., `Production`, `Beta`, `Internal`). Test new bundles safely before promoting them to your entire user base.
- **Bundle Versioning & Uploads**: Upload zipped HTML/JS/CSS web assets as immutable bundles. Track file sizes, versions, and deployment timelines automatically.
- **Device Fleet Tracking**: Automatically track devices checking in for updates. Monitor device platforms (iOS, Android, Web), UUIDs, last active timestamps, and trace exactly which bundle version they are currently running.
- **Advanced API Token Management**: Issue secure, non-expiring (or expiring) Laravel Sanctum API Tokens to authenticate your CI/CD pipelines or client apps. Includes a bespoke UI to securely store and 1-click copy plain-text tokens right from the browser.
- **Admin Panel**: A beautiful, responsive, and mobile-friendly Filament v4 interface that requires zero frontend configuration to get started.

## 💡 Benefits

- **Self-Hosted & Private**: Retain 100% control of your users' data, your proprietary source code bundles, and your infrastructure.
- **Cost-Effective**: Avoid expensive MAU (Monthly Active User) pricing models commonly used by enterprise live-update providers.
- **Instant Deployments**: Bypass the iOS App Store and Google Play Store review processes for standard HTML/JS/CSS codebase changes. Ship bug fixes to users instantly.
- **Extensible**: Because it's built on Laravel, adding custom OAuth integrations, webhooks, or S3 bucket offloading is native and seamless.

---

## 🔮 Future Development Recommendations

To evolve the project further, the following additions are recommended for future milestones:

1. **Client API Endpoints (`/api/check` and `/api/download`)**
   - Implement the actual public-facing REST API routes that the frontend Capacitor mobile app will query to discover if a new bundle exists for their assigned channel, and download the `.zip` securely.

2. **Canary / Phased Rollouts**
   - Add a `rollout_percentage` field to Bundles. This allows gradually upgrading a percentage of devices (e.g., 10%) automatically before committing to a 100% rollout to test for regression.

3. **CI/CD CLI Tooling (`laracap-cli`)**
   - Build an NPM CLI package that developers can run inside GitHub Actions or GitLab CI to automatically build the web assets, zip them, and push them directly to this Laravel backend using the Sanctum API Tokens.

4. **Webhooks & Notifications**
   - Add native webhooks to broadcast to Slack, Discord, or generic endpoints whenever a new bundle is pushed or a channel's active bundle is rotated.

5. **Team Workspaces & RBAC**
   - Introduce Laravel teams or Spatie Permissions. Allow multiple developers to manage the same `Application` without requiring super-admin privileges.

6. **S3 / CDN Offloading**
   - By default, bundles are stored locally in the `public` disk. Transition the storage mechanism strictly to Amazon S3 or Cloudflare R2 for enhanced redundancy and edge-caching speed.

## License

This custom management panel is open-source software built utilizing the [Laravel framework](https://laravel.com).
