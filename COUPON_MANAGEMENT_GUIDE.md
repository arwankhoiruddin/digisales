# Coupon Management Guide

## How to Access Coupon Management

After activating the DigiSales plugin, you can manage coupons through the WordPress admin dashboard.

### Navigation Path

1. Log in to your WordPress Admin Dashboard
2. Look for **DigiSales** in the left sidebar menu
3. Hover over **DigiSales** to see the submenu
4. Click on **Coupons**

**Menu Structure:**
```
WordPress Admin Dashboard
├── Dashboard
├── Posts
├── Media
├── Pages
├── Comments
├── DigiSales  ← Click here
│   ├── Dashboard
│   ├── Products
│   ├── Add New Product
│   ├── Orders
│   ├── Coupons  ← Then click here
│   └── Settings
```

## Adding a New Coupon

Once on the Coupons page, you'll see a form titled **"Add New Coupon"**.

### Required Fields

1. **Coupon Code** (Required)
   - Enter a unique code (e.g., "SAVE20", "SUMMER2024")
   - This is what customers will enter at checkout

2. **Discount Type** (Required)
   - Select either:
     - **Percentage** - Discount as a percentage (e.g., 20%)
     - **Fixed Amount** - Discount as a dollar amount (e.g., $15)

3. **Discount Value** (Required)
   - Enter the discount amount
   - For Percentage: Enter just the number (e.g., 20 for 20%)
   - For Fixed Amount: Enter the dollar value (e.g., 15 for $15)

### Optional Fields

4. **Start Date** (Optional)
   - When the coupon becomes active
   - Leave blank for immediate activation

5. **End Date** (Optional)
   - When the coupon expires
   - Leave blank for no expiration

6. **Maximum Usage** (Optional)
   - Limit how many times the coupon can be used
   - Leave blank for unlimited usage

7. **Applicable Products** (Optional)
   - Select specific products this coupon applies to
   - Hold Ctrl/Cmd to select multiple products
   - Leave empty or select "All Products" for site-wide coupon

### Example: Creating a 20% Off Coupon

Here's a step-by-step example:

```
Field               | Value
--------------------|------------------
Coupon Code         | SAVE20
Discount Type       | Percentage
Discount Value      | 20
Start Date          | (leave blank or set date)
End Date            | (leave blank or set date)
Maximum Usage       | 100
Applicable Products | All Products
```

Click **"Add Coupon"** button to save.

## Viewing Existing Coupons

Below the "Add New Coupon" form, you'll see a table titled **"Existing Coupons"** that displays:

- **Code**: The coupon code
- **Type**: Percentage or Fixed
- **Value**: The discount amount (e.g., "20%" or "$15.00")
- **Valid Period**: Date range or "Anytime"
- **Usage**: Current usage count / Maximum allowed (e.g., "45 / 100" or "12 / Unlimited")
- **Actions**: Delete button to remove the coupon

## Managing Coupons

### Editing a Coupon
Currently, to edit a coupon:
1. Delete the existing coupon
2. Create a new coupon with updated values

### Deleting a Coupon
1. Find the coupon in the "Existing Coupons" table
2. Click the **"Delete"** button in the Actions column
3. Confirm the deletion when prompted

## Coupon Validation

The system automatically validates:
- ✅ Coupon existence
- ✅ Date validity (active within start/end date range)
- ✅ Usage limits (not exceeded maximum usage)
- ✅ Product applicability (if restricted to specific products)

## Troubleshooting

### "I don't see the Coupons menu item"

**Possible causes:**
1. Plugin not activated - Go to Plugins → Installed Plugins and activate DigiSales
2. Insufficient permissions - You need to be logged in as an Administrator
3. Browser cache - Clear your browser cache and refresh the page

**Solution:**
- Make sure you're logged in as an Administrator
- Navigate to: **Plugins → Installed Plugins**
- Find "DigiSales" and click "Activate" if not already active
- Refresh your browser (Ctrl+F5 or Cmd+Shift+R)
- The DigiSales menu should now appear in the left sidebar

### "I created a coupon but it's not working"

**Check the following:**
1. Coupon code spelling - Must match exactly (case-sensitive)
2. Date validity - Current date must be within start/end dates
3. Usage limit - May have reached maximum usage
4. Product restriction - Coupon may be limited to specific products
5. Database table - Ensure the coupons table was created during plugin activation

**To verify coupon table exists:**
Use a database tool (like phpMyAdmin) and check for table: `wp_digisales_coupons`

### "How do I reactivate the plugin to create database tables?"

1. Go to **Plugins → Installed Plugins**
2. Find "DigiSales"
3. Click "Deactivate"
4. Click "Activate"

This will run the activation hook and create necessary database tables.

## Visual Reference

### Admin Menu Location
```
┌─────────────────────────────────────────┐
│ WordPress Admin Sidebar                 │
├─────────────────────────────────────────┤
│ 📊 Dashboard                            │
│ 📝 Posts                                │
│ 🖼️  Media                               │
│ 📄 Pages                                │
│ 💬 Comments                             │
│ 🛒 DigiSales  ◄── CLICK HERE           │
│    ├─ Dashboard                         │
│    ├─ Products                          │
│    ├─ Add New Product                   │
│    ├─ Orders                            │
│    ├─ Coupons  ◄── THEN CLICK HERE     │
│    └─ Settings                          │
│ 👥 Users                                │
│ 🔧 Tools                                │
│ ⚙️  Settings                            │
└─────────────────────────────────────────┘
```

### Coupons Page Layout
```
┌───────────────────────────────────────────────────────────┐
│ Coupons                                                    │
├───────────────────────────────────────────────────────────┤
│                                                            │
│ Add New Coupon                                            │
│ ────────────────────────────────────────────────────────  │
│                                                            │
│ Coupon Code:      [SAVE20________________]                │
│ Discount Type:    [Percentage ▼]                          │
│ Discount Value:   [__20.00___]                            │
│ Start Date:       [2024-01-01T00:00] (optional)           │
│ End Date:         [2024-12-31T23:59] (optional)           │
│ Maximum Usage:    [__100_____] (optional)                 │
│ Applicable:       [All Products ▼] (multi-select)         │
│                                                            │
│                   [ Add Coupon ]                           │
│                                                            │
├───────────────────────────────────────────────────────────┤
│                                                            │
│ Existing Coupons                                          │
│ ────────────────────────────────────────────────────────  │
│                                                            │
│ Code    | Type       | Value | Valid Period    | Usage   │
│ ──────────────────────────────────────────────────────── │
│ SAVE20  | Percentage | 20%   | 2024-01-01 to  | 45 / 100│
│         |            |       | 2024-12-31      | [Delete]│
│ ──────────────────────────────────────────────────────── │
│ SUMMER  | Fixed      | $15   | Anytime         | 12 / ∞  │
│         |            |       |                 | [Delete]│
│ ──────────────────────────────────────────────────────── │
│                                                            │
└───────────────────────────────────────────────────────────┘
```

## Quick Start Checklist

- [ ] Activate DigiSales plugin
- [ ] Log in as Administrator
- [ ] Navigate to DigiSales → Coupons
- [ ] Fill in the coupon form
- [ ] Click "Add Coupon"
- [ ] Verify coupon appears in the table below
- [ ] Test coupon on a product page

## Additional Resources

For more detailed information, see:
- `FLEXIBLE_PRICING.md` - Complete feature documentation
- `IMPLEMENTATION_SUMMARY.md` - Technical implementation details
- `TESTING_CHECKLIST.md` - Testing guide

## Need Help?

If you're still unable to see the Coupons management interface:
1. Verify you're running WordPress 5.8 or higher
2. Check that the DigiSales plugin is activated
3. Confirm you're logged in with Administrator privileges
4. Check browser console for JavaScript errors
5. Try a different browser or clear browser cache
