# hack-genesis-2026-the-tech-titans
Hackathon team repository for The Tech Titans - [hackindia-team:hack-genesis-2026:the-tech-titans]
My Highway — Highway Assistance Platform
My Highway is a web-based highway assistance platform designed to help drivers request roadside services and connect with service providers. It includes separate customer, service-provider, and admin panels, with a PHP API and MySQL database.
Features
- Customer panel: View/request services, manage profile, and contact support.
- Service-provider panel: Access the provider dashboard, manage profile, and handle service requests.
- Admin panel: View and manage users, services, and support-related information.
- Authentication: Registration and login pages for platform users.
- Service requests: Store request details and statuses in MySQL.
- Location support: Browser geolocation can be used for location-aware features when permission is granted.
- AI assistant: Includes a built-in assistant; optional Anthropic API integration can be configured for enhanced replies.
- Responsive interface: HTML, CSS, and JavaScript frontend.
Note: Location access generally requires localhost or HTTPS and the user's permission. Map tiles and optional AI integration require an internet connection. Availability of individual features depends on configuration and testing in your environment.

Technology Stack
- Frontend: HTML5, CSS3, JavaScript
- Backend: PHP 8+
- Database: MySQL
- Local development: XAMPP or WAMP
Project Structure
highway-assist/
├── admin/              # Admin dashboard pages
├── customer/           # Customer dashboard pages
├── provider/           # Service-provider dashboard pages
├── assets/
│   ├── app.js          # Shared JavaScript
│   └── style.css       # Shared styles
├── uploads/            # Uploaded files
├── api.php             # JSON API and backend logic
├── config.php          # Database and optional AI configuration
├── database.sql        # Database schema
├── index.html          # Landing page
├── login.html          # Login page
├── register.html       # Registration page
└── install.php         # Initial setup script
Run Locally with XAMPP
1. Install and open XAMPP.
2. Start Apache and MySQL.
3. Copy the project folder into XAMPP's htdocs directory. For example:
   C:\xampp\htdocs\highway-assist
4. Open phpMyAdmin at http://localhost/phpmyadmin and make sure the database settings in config.php match your local MySQL setup. The default configuration uses database highway_assist, user root, and an empty password.
5. In your browser, open:
   http://localhost/highway-assist/install.php
6. After installation completes, delete install.php from the project directory for security.
7. Open the website:
   http://localhost/highway-assist/index.html
If the installer does not create the database automatically, create a database named highway_assist in phpMyAdmin and import database.sql, then run the installer again if required.
Demo Accounts
The original project notes include these demo credentials:
Role	Email	Password
Admin	admin@highway.com	admin123
Service provider	provider@highway.com	provider123
Customer	customer@highway.com	customer123


These are for local demonstration only. Change or remove demo accounts before deploying the application publicly. Do not use these credentials on a live deployment.
Optional AI Configuration
The built-in assistant works without an external API key. To configure optional Anthropic integration, add your API key to the server-side configuration in config.php and verify the configured model and API implementation before use. Never commit real API keys or passwords to a public repository. Prefer environment variables or a private server-side configuration file for production.
Security Notes
- Remove install.php after setup.
- Do not commit database passwords, API keys, access tokens, or private user data.
- Change all demo credentials before deployment.
- Use HTTPS in production, especially for login and location features.
- Review upload validation, authorization, and database configuration before making the application public.
Future Improvements
- Production-ready maps and live provider tracking
- Notifications and request status updates
- Stronger deployment configuration and automated tests
- Enhanced AI support and customer-service workflows
Project Purpose
This project explores how a digital platform can make roadside assistance requests easier to manage by connecting customers, service providers, and administrators through a shared web application.
License
See the LICENSE file for licensing terms.
