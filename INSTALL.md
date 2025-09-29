# Installation Instructions

## How to install the DigiSales WordPress Plugin

### Requirements
- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL/MariaDB database
- Required PHP extensions: curl, json, mbstring

### Installation Steps

1. **Upload Plugin Files**
   - Download or clone this repository
   - Upload the entire `digisales` folder to your WordPress `/wp-content/plugins/` directory
   - Alternatively, create a ZIP file of the plugin folder and upload via WordPress admin

2. **Activate the Plugin**
   - Go to your WordPress Admin Dashboard
   - Navigate to **Plugins** > **Installed Plugins**
   - Find "DigiSales" in the list
   - Click **Activate**

3. **Access the Plugin**
   - After activation, you'll see a new **DigiSales** menu item in your WordPress admin sidebar
   - The menu includes:
     - Dashboard (main overview)
     - Products (digital product management)
     - Orders (sales and transaction management)
     - Settings (plugin configuration)

4. **Initial Setup**
   - Go to **DigiSales** > **Settings**
   - Enter your license key (if you have one)
   - Configure your payment gateway settings
   - Set up your digital product categories

### Plugin Structure

```
digisales/
├── digisales.php          # Main plugin file
├── assets/
│   ├── css/
│   │   └── admin.css      # Admin interface styling
│   └── js/                # JavaScript files (future)
├── languages/
│   └── digisales.pot      # Language template
├── README.md              # Project documentation
└── .gitignore            # Git ignore rules
```

### Features Included

- **Admin Menu System**: Complete navigation structure for managing digital products
- **Custom Post Type**: Dedicated post type for digital products
- **Database Integration**: Order tracking and customer management
- **Security**: Proper WordPress security implementation
- **Internationalization**: Multi-language support ready
- **Admin Interface**: Clean, WordPress-standard admin pages

### Next Steps

After installation, you can:
1. Create your first digital product
2. Set up payment gateways
3. Configure download security settings
4. Customize the customer interface

### Support

For technical support or questions, please contact: support@yourdomain.com