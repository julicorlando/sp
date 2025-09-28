# Sistema de Pacientes - PHP Migration

This project has been migrated from Django/Python to pure PHP with MySQL.

## Migration Overview

The patient management system has been completely converted from Django framework to pure PHP while maintaining all original functionality and design.

### Original System (Django)
- **Framework**: Django 5.1.1
- **Database**: MySQL (already configured)
- **Features**: Patient CRUD, Payments, File uploads, Evolution tracking, User authentication

### New System (PHP)
- **Technology**: Pure PHP 7.4+ with PDO
- **Database**: MySQL (same database structure)
- **Features**: All original features preserved

## Installation Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- At least 10MB upload limit

### Database Setup
1. Create the database using the provided schema:
```bash
mysql -u username -p < database.sql
```

2. Update database credentials in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'stepsi_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### File Permissions
```bash
# Make upload directories writable
chmod 755 media/
chmod 755 static/
```

### Default Login
- **Username**: admin
- **Password**: admin123

*Change the default password immediately after first login.*

## File Structure

```
/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── auth.php             # Authentication system
│   ├── functions.php        # Utility functions
│   ├── header.php           # Common header
│   └── footer.php           # Common footer
├── static/                  # CSS, JS, images (preserved from Django)
├── media/                   # User uploads
├── database.sql             # Database schema
├── .htaccess               # Apache configuration
├── index.php               # Homepage
├── login.php               # Login page
├── register.php            # User registration
├── dashboard.php           # Patient listing
├── patient_*.php           # Patient management
├── payment_*.php           # Payment management
├── evolution_*.php         # Evolution tracking
└── file_*.php              # File management
```

## Features

### User Management
- ✅ User registration and login
- ✅ Session-based authentication
- ✅ CSRF protection on all forms
- ✅ Password hashing and validation

### Patient Management
- ✅ Add new patients with comprehensive form
- ✅ Edit existing patient information
- ✅ View detailed patient information
- ✅ Delete patients with confirmation
- ✅ Search/filter patients
- ✅ User isolation (users see only their patients)

### Payment System
- ✅ Add payments for patients
- ✅ View payment history
- ✅ Delete payments
- ✅ Payment summaries

### Evolution Tracking
- ✅ Add patient evolution notes
- ✅ Edit evolution entries
- ✅ View evolution history
- ✅ Delete evolution entries

### File Management
- ✅ Upload files for patients
- ✅ Download uploaded files
- ✅ Delete uploaded files
- ✅ File organization by patient

### Security Features
- ✅ CSRF token protection
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS protection (input sanitization)
- ✅ File upload validation
- ✅ Session security
- ✅ Access control (user isolation)

## Database Schema

### Tables
- `users` - User accounts
- `pacientes` - Patient information
- `pagamentos` - Payment records
- `arquivos` - File uploads
- `evolucoes` - Evolution notes

All tables include proper foreign key relationships and indexes for performance.

## Migration Benefits

1. **Simplified Deployment**: No Python dependencies or virtual environments
2. **Better Performance**: Direct PHP execution without framework overhead
3. **Easier Maintenance**: Straightforward PHP code structure
4. **Cost Effective**: Runs on standard shared hosting
5. **Security**: Built-in security measures and validation
6. **Compatibility**: Works with standard LAMP stack

## Development Notes

### Form Validation
All forms include both client-side and server-side validation:
- Required field validation
- Data type validation (email, phone, CPF)
- CSRF token verification
- File upload validation

### Error Handling
- Database errors are caught and handled gracefully
- User-friendly error messages
- Flash message system for feedback

### Code Organization
- Separation of concerns with includes/ directory
- Reusable functions in functions.php
- Consistent code style and documentation
- Security-first approach

## Support

For any issues or questions regarding the migration:
1. Check the error logs in your server
2. Verify database connection in config/database.php
3. Ensure proper file permissions
4. Review PHP error logs

## Security Recommendations

1. Change default admin password immediately
2. Use HTTPS in production
3. Regular database backups
4. Keep PHP updated
5. Monitor upload directory for security
6. Regular security audits

---

**Migration completed**: All Django functionality successfully converted to PHP
**Original design preserved**: All HTML/CSS maintained exactly as in Django version
**Database compatibility**: Uses same MySQL database structure as Django# sp
