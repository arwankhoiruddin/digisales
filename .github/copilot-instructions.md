# Copilot Instructions for digisales

## Project Overview

**digisales** is a proprietary, closed-source WordPress plugin designed to transform WordPress installations into complete digital product sales platforms. The plugin enables creators to securely sell digital assets such as videos, e-books, video series, and other downloadable content directly from their website.

## Key Architecture & Components

### Core Functionality
- **Custom Post Type (CPT)**: Uses WordPress CPT for organizing and managing digital products separately from standard content
- **Secure Content Protection**: Implements time-limited, expiring download links to prevent unauthorized file sharing
- **Payment Gateway Integration**: Supports Stripe, PayPal, Midtrans, and other payment processors
- **Sales Tracking**: Admin dashboard for monitoring customer transactions and order history

### Product Management
- **Flexible Pricing**: Fixed pricing, scheduled discounts, and promotional coupon codes
- **Digital Asset Upload**: Integration with WordPress media library
- **Cloud Offloading**: Options for AWS S3 integration for large media files
- **Customer Library**: Frontend "My Account" section for purchased content access

## System Requirements
- WordPress 5.8+
- PHP 7.4+
- MySQL/MariaDB
- Required PHP extensions: `curl`, `json`, `mbstring`

## Development Guidelines

### Code Style & Standards
- Follow WordPress coding standards and best practices
- Maintain compatibility with specified WordPress and PHP versions
- Ensure security-first approach for handling digital assets and payments
- Use WordPress hooks and filters appropriately

### Security Considerations
- Implement secure file delivery mechanisms
- Protect against unauthorized access to digital content
- Validate and sanitize all user inputs
- Follow WordPress security guidelines for plugin development

### Plugin Structure
- Utilize WordPress plugin architecture patterns
- Implement proper activation/deactivation hooks
- Use WordPress admin interface conventions
- Follow WordPress database table naming conventions

### Key Features to Consider
When working on this plugin, always consider:
1. **License Management**: Plugin requires license key activation
2. **Payment Security**: All payment processing must be secure and compliant
3. **File Protection**: Digital assets must be protected from direct access
4. **User Experience**: Maintain intuitive WordPress admin interface
5. **Performance**: Handle large digital files efficiently
6. **Compatibility**: Ensure compatibility with various WordPress themes and plugins

## Common Tasks

### Adding New Features
- Ensure compatibility with existing Custom Post Type structure
- Maintain security standards for file handling
- Follow WordPress admin UI patterns
- Test with multiple payment gateways

### Bug Fixes
- Always test with minimum system requirements
- Verify security implications of any changes
- Test download link generation and expiration
- Validate payment gateway integrations

### Testing Considerations
- Test plugin activation/deactivation
- Verify license key validation
- Test digital asset upload and delivery
- Validate payment processing workflows
- Check customer access controls

## Support & Licensing
This is a **proprietary, closed-source** plugin. All modifications must comply with the commercial license agreement. Unauthorized distribution is prohibited.

For technical support: `support@yourdomain.com`