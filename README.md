# Car Detailing Pro - Booking and Management System

A comprehensive web-based car detailing booking and management system with user accounts, vehicle garage management, and administrative dashboard.

## Features

### Customer Features
- **User Registration & Authentication**
  - Secure user registration with email verification
  - Login/logout functionality
  - Password reset capability
  - Profile management

- **Vehicle Garage Management**
  - Add, edit, and delete vehicles
  - Set default vehicle
  - Store vehicle details (make, model, year, type, color, license plate, VIN)
  - Vehicle nicknames for easy identification

- **Booking System**
  - Multi-step booking process
  - Service package selection
  - Vehicle selection from garage
  - Date and time scheduling
  - Mobile or facility service options
  - Real-time booking summary

- **Appointment Management**
  - View all appointments (upcoming and past)
  - Change vehicle for upcoming appointments
  - Booking status tracking
  - Service history

### Admin Features
- **Comprehensive Dashboard**
  - Business statistics and metrics
  - User management
  - Booking management
  - Service management
  - Revenue tracking

- **User Management**
  - View all registered users
  - User profile details
  - Vehicle garage access
  - Booking history per user

- **Booking Management**
  - View all bookings with filtering
  - Update booking status
  - Add admin notes
  - Booking details with customer and vehicle information

- **Service Management**
  - Add/edit/delete services
  - Service categories (packages/add-ons)
  - Pricing management
  - Service activation/deactivation

## Technical Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Features**: Responsive design, AJAX, form validation, security features

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional, for dependency management)

### Setup Instructions

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   cd car-detailing-system
   ```

2. **Database Configuration**
   - Create a new MySQL database
   - Update database credentials in `database/config.php`:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'car_detailing_db');
     ```

3. **Web Server Configuration**
   - Point your web server to the project directory
   - Ensure PHP has write permissions for session handling
   - Configure URL rewriting if needed

4. **Initial Setup**
   - Access the application in your browser
   - The system will automatically create the database tables
   - Default admin account will be created:
     - Email: `admin@cardetailing.com`
     - Password: `admin123`

5. **Email Configuration (Optional)**
   - Update the `send_email()` function in `includes/functions.php`
   - Configure SMTP settings for email verification and password reset

## File Structure

```
car-detailing-system/
├── index.php                 # Main entry point
├── verify.php               # Email verification
├── reset_password.php       # Password reset
├── config/
│   └── database.php         # Database configuration
├── includes/
│   └── functions.php        # Helper functions
├── pages/
│   ├── home.php            # Home page
│   ├── login.php           # Login page
│   ├── register.php        # Registration page
│   ├── profile.php         # User profile
│   ├── booking.php         # Booking system
│   ├── admin.php           # Admin dashboard
│   ├── services.php        # Services page
│   ├── about.php           # About page
│   └── contact.php         # Contact page
├── ajax/
│   ├── get_user_vehicles.php
│   ├── update_booking_vehicle.php
│   ├── get_bookings.php
│   ├── get_users.php
│   └── get_services.php
└── assets/
    ├── css/
    │   └── style.css       # Custom styles
    └── js/
        └── script.js       # JavaScript functionality
```

## Database Schema

### Users Table
- User registration and authentication
- Email verification system
- Password reset functionality

### Vehicles Table
- Vehicle information linked to users
- Default vehicle designation
- Comprehensive vehicle details

### Services Table
- Service packages and add-ons
- Pricing and duration information
- Service categories

### Bookings Table
- Appointment scheduling
- Service and vehicle associations
- Status tracking and admin notes

### Booking Services Table
- Many-to-many relationship between bookings and services
- Service pricing at time of booking

## Security Features

- **Password Security**: Bcrypt hashing with salt
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization and output escaping
- **CSRF Protection**: Form tokens and session validation
- **Email Verification**: Required account activation
- **Session Management**: Secure session handling

## Usage Guide

### For Customers

1. **Registration**
   - Visit the website and click "Register"
   - Fill in personal information
   - Verify email address
   - Log in to your account

2. **Adding Vehicles**
   - Go to "My Profile" → "My Garage"
   - Click "Add Vehicle"
   - Fill in vehicle details
   - Set as default if desired

3. **Booking Services**
   - Click "Book Now" or "Book Your Appointment"
   - Select services (packages and add-ons)
   - Choose vehicle from garage
   - Select date, time, and service type
   - Review and confirm booking

4. **Managing Appointments**
   - View appointments in "My Profile"
   - Change vehicle for upcoming appointments
   - Track booking status

### For Administrators

1. **Dashboard Access**
   - Log in with admin credentials
   - Access comprehensive business overview

2. **User Management**
   - View all registered users
   - Access user profiles and vehicle garages
   - Monitor user activity

3. **Booking Management**
   - View and filter all bookings
   - Update booking status
   - Add admin notes
   - Track revenue and metrics

4. **Service Management**
   - Add new services
   - Modify pricing and descriptions
   - Activate/deactivate services

## Customization

### Styling
- Modify `assets/css/style.css` for custom styling
- Update Bootstrap theme colors
- Customize component styles

### Functionality
- Extend `includes/functions.php` with additional helper functions
- Modify AJAX handlers in `ajax/` directory
- Add new pages in `pages/` directory

### Database
- Add new tables for additional features
- Modify existing schema as needed
- Update queries in relevant files

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `database/config.php`
   - Ensure MySQL service is running
   - Check database permissions

2. **Email Not Working**
   - Update email configuration in `includes/functions.php`
   - Check server email settings
   - Verify SMTP credentials

3. **Session Issues**
   - Ensure PHP has write permissions
   - Check session configuration
   - Verify session storage path

4. **AJAX Errors**
   - Check browser console for JavaScript errors
   - Verify file paths in AJAX calls
   - Ensure proper error handling

### Performance Optimization

1. **Database Optimization**
   - Add indexes to frequently queried columns
   - Optimize complex queries
   - Implement database caching

2. **Frontend Optimization**
   - Minify CSS and JavaScript
   - Optimize images
   - Implement lazy loading

3. **Server Optimization**
   - Enable PHP OPcache
   - Configure proper caching headers
   - Optimize web server configuration

## Support

For technical support or feature requests, please contact the development team or create an issue in the project repository.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Changelog

### Version 1.0.0
- Initial release
- Complete booking system
- User management
- Admin dashboard
- Vehicle garage functionality
- Email verification system
- Responsive design

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Acknowledgments

- Bootstrap for the responsive framework
- Font Awesome for icons
- PHP community for best practices
- MySQL documentation for database optimization
