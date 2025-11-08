# Donation & Help Request System

A comprehensive web application that connects individuals with organizations for donations and help requests. Built with HTML, CSS, JavaScript, and PHP with MySQL database.

## Features

### For Individuals:
- **Registration & Login**: Separate authentication system for individuals
- **Make Donations**: Donate money to registered organizations
- **Request Help**: Submit help requests to organizations
- **Dashboard**: View donation history and help request status
- **Real-time Updates**: Track the status of donations and requests

### For Organizations:
- **Registration & Login**: Separate authentication system for organizations
- **Receive Donations**: Accept or reject donations from individuals
- **Manage Help Requests**: Approve or reject help requests
- **Dashboard**: View statistics, donations received, and help requests
- **Analytics**: Track total donations and request management

## System Requirements

- **Web Server**: Apache/Nginx with PHP support
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **Browser**: Modern web browser with JavaScript enabled

## Installation Instructions

### 1. Database Setup
1. Create a MySQL database named `donation_system`
2. Import the database schema from `database_setup.sql`
3. Update database credentials in PHP files if needed (default: localhost, root, no password)

### 2. File Setup
1. Place all files in your web server directory (e.g., `htdocs` for XAMPP)
2. Ensure proper file permissions are set
3. Make sure PHP sessions are enabled

### 3. Configuration
1. Update database connection settings in PHP files if your MySQL setup differs:
   - Host: `localhost`
   - Database: `donation_system`
   - Username: `root`
   - Password: (empty by default)

### 4. Access the System
1. Open your web browser
2. Navigate to `http://localhost/SYSTEM/core/index.php`
3. Choose your user type (Individual or Organization)
4. Register a new account or login with existing credentials

## Modular Architecture

This system is organized into logical modules for better maintainability and organization:

### Core Module (`core/`)
- **index.php**: Main landing page with user type selection
- **logout.php**: Session management and logout functionality

### Authentication Module (`auth/`)
- Handles all user authentication (login/registration)
- Separate flows for individuals and organizations
- Secure password hashing and session management

### Dashboard Module (`dashboard/`)
- User-specific dashboards for both user types
- Individual dashboard: donations and help requests
- Organization dashboard: donation management and photo uploads

### Processing Module (`processing/`)
- Backend processing for all user actions
- Donation processing and status management
- Help request processing and status updates
- Photo upload and deletion handling

### Assets Module (`assets/`)
- Static files including CSS stylesheets
- File upload storage directory
- Organized upload structure for different file types

### Database Module (`database/`)
- Database schema and configuration
- Sample data for testing and development
- Database setup scripts

### API Module (`api/`)
- API endpoints for system functionality

## File Structure

```
SYSTEM/
├── core/                                 # Core application files
│   ├── index.php                         # Main landing page
│   └── logout.php                        # Logout functionality
├── auth/                                 # Authentication module
│   ├── individual_login.php              # Individual login form
│   ├── organization_login.php            # Organization login form
│   ├── individual_register.php           # Individual registration form
│   ├── organization_register.php         # Organization registration form
│   ├── process_individual_login.php      # Individual login processing
│   ├── process_organization_login.php    # Organization login processing
│   ├── process_individual_registration.php # Individual registration processing
│   └── process_organization_registration.php # Organization registration processing
├── dashboard/                            # User dashboard module
│   ├── individual_dashboard.php          # Individual dashboard
│   └── organization_dashboard.php        # Organization dashboard
├── processing/                           # Backend processing module
│   ├── process_donation.php              # Donation processing
│   ├── process_help_request.php          # Help request processing
│   ├── process_donation_status.php      # Donation status management
│   ├── process_request_status.php        # Help request status management
│   ├── process_photo_upload.php          # Photo upload processing
│   └── process_photo_delete.php         # Photo deletion processing
├── assets/                               # Static assets
│   ├── styles.css                        # Main stylesheet
│   └── uploads/                          # File uploads directory
│       └── documentary_photos/           # Documentary photos storage
├── api/                                  # API endpoints
├── database/                             # Database configuration
│   └── database_setup.sql                # Database schema and sample data
└── README.md                             # Project documentation
```

## Database Schema

### Tables:
- **individuals**: Stores individual user information
- **organizations**: Stores organization information
- **donations**: Tracks donations made by individuals to organizations
- **help_requests**: Manages help requests from individuals

### Sample Data:
- Pre-loaded with sample organizations (Red Cross Foundation, Food Bank Network)
- Sample individual user (alice@example.com, password: password123)

## Usage Guide

### For Individuals:
1. **Register**: Create an account with personal information
2. **Login**: Access your dashboard
3. **Donate**: Select an organization and specify donation amount
4. **Request Help**: Submit help requests with title, description, and optional amount
5. **Track**: Monitor donation and request status

### For Organizations:
1. **Register**: Create organization account with contact details
2. **Login**: Access organization dashboard
3. **Manage Donations**: Accept or reject incoming donations
4. **Handle Requests**: Approve or reject help requests
5. **Analytics**: View donation statistics and request management

## Security Features

- **Password Hashing**: All passwords are securely hashed using PHP's password_hash()
- **SQL Injection Protection**: Prepared statements used throughout
- **Session Management**: Secure session handling for user authentication
- **Input Validation**: Server-side validation for all form inputs
- **XSS Protection**: HTML entities escaped for output

## Customization

### Styling:
- Modify `styles.css` to change the appearance
- Responsive design included for mobile devices
- Modern gradient design with hover effects

### Functionality:
- Add email notifications by integrating with SMTP
- Implement payment gateway integration for real donations
- Add file upload functionality for help requests
- Include advanced reporting and analytics

## Troubleshooting

### Common Issues:
1. **Database Connection Error**: Check MySQL service and credentials
2. **Session Issues**: Ensure PHP sessions are enabled
3. **Permission Errors**: Check file permissions on web server
4. **CSS Not Loading**: Verify file paths and web server configuration

### Support:
- Check PHP error logs for detailed error messages
- Ensure all required PHP extensions are installed
- Verify MySQL database is running and accessible

## License

This project is open source and available under the MIT License.

## Contributing

Feel free to contribute to this project by:
- Reporting bugs
- Suggesting new features
- Submitting pull requests
- Improving documentation

---

**Note**: This is a demonstration system. For production use, implement additional security measures, payment processing, and comprehensive testing.
