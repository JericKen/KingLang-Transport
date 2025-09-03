# Slideshow Management System

This document describes the new dynamic slideshow management system for KingLang Transport.

## Overview

The slideshow management system allows administrators to:
- Upload new slideshow images
- Edit image titles, descriptions, and display order
- Activate/deactivate images
- Reorder images using drag-and-drop
- Delete images (with automatic file cleanup)

## Features

### Admin Panel
- **Slideshow Management Page**: `/admin/slideshow`
- **Image Upload**: Support for JPG, PNG, GIF (max 5MB)
- **Image Management**: Edit, delete, activate/deactivate images
- **Drag & Drop Reordering**: Change display order by dragging images
- **Statistics**: View total, active, and inactive image counts

### Frontend Integration
- **Dynamic Loading**: Images are loaded from the database
- **Automatic Updates**: Changes in admin panel immediately reflect on frontend
- **Fallback Support**: Graceful fallback to default images if API fails
- **Responsive Design**: Works on all device sizes

## Database Setup

Run the following SQL to create the required table:

```sql
-- Run the file: database/add_slideshow_management.sql
```

This creates the `slideshow_images` table with the following structure:
- `id`: Primary key
- `filename`: Stored filename on server
- `original_filename`: Original uploaded filename
- `title`: Image title (displayed as slideshow text)
- `description`: Image description
- `display_order`: Order of appearance (lower numbers first)
- `is_active`: Whether image is visible in slideshow
- `created_at`: Upload timestamp
- `updated_at`: Last modification timestamp
- `created_by`: Admin user who uploaded the image

## File Structure

```
app/
├── controllers/admin/
│   └── SlideshowManagementController.php    # Admin slideshow management
├── controllers/client/
│   └── SlideshowController.php              # Frontend slideshow API
├── models/admin/
│   └── SlideshowManagementModel.php         # Database operations
└── views/admin/
    └── slideshow_management.php             # Admin interface

public/
├── js/
│   ├── admin/
│   │   └── slideshow_management.js          # Admin panel JavaScript
│   └── dynamic-slideshow.js                 # Frontend slideshow loader
└── css/
    └── slideshow.css                        # Slideshow styling (updated)

database/
└── add_slideshow_management.sql             # Database setup script
```

## Routes

### Admin Routes
- `GET /admin/slideshow` - Slideshow management page
- `GET /admin/slideshow/list` - Get all slideshow images
- `POST /admin/slideshow/upload` - Upload new image
- `POST /admin/slideshow/update` - Update image details
- `POST /admin/slideshow/delete` - Delete image
- `POST /admin/slideshow/toggle-status` - Activate/deactivate image
- `POST /admin/slideshow/update-order` - Update display order
- `GET /admin/slideshow/stats` - Get slideshow statistics

### Frontend Routes
- `GET /api/slideshow/images` - Get active slideshow images

## Usage

### For Administrators

1. **Access Slideshow Management**:
   - Go to `/admin/slideshow`
   - Or click "Slideshow" in the admin sidebar

2. **Upload New Image**:
   - Click "Upload Image" button
   - Select image file (JPG, PNG, or GIF)
   - Add title and description (optional)
   - Set display order
   - Click "Upload Image"

3. **Manage Existing Images**:
   - **Edit**: Click "Edit" button to modify title, description, order, or status
   - **Activate/Deactivate**: Use toggle button to show/hide images
   - **Reorder**: Drag and drop images to change display order
   - **Delete**: Click "Delete" button (removes both database record and file)

### For Developers

1. **Initialize Dynamic Slideshow**:
   ```html
   <div class="slideshow-container"></div>
   <script src="public/js/dynamic-slideshow.js"></script>
   ```

2. **Customize Options**:
   ```javascript
   new DynamicSlideshow('.slideshow-container', {
       autoPlay: true,
       interval: 5000,
       showText: true,
       showContactInfo: false
   });
   ```

3. **Refresh Slideshow**:
   ```javascript
   const slideshow = new DynamicSlideshow('.slideshow-container');
   slideshow.refresh(); // Reload images from database
   ```

## Configuration

### Image Requirements
- **Formats**: JPG, PNG, GIF
- **Max Size**: 5MB
- **Recommended Dimensions**: 1920x1080 or similar aspect ratio
- **Storage Location**: `public/images/slideshow/`

### Display Settings
- **Auto-play**: Enabled by default
- **Transition Duration**: 1.5 seconds
- **Auto-play Interval**: 5-6 seconds (configurable per page)
- **Text Display**: Configurable per page type

## Security Features

- **File Validation**: Type and size checking
- **Unique Filenames**: Prevents filename conflicts
- **Admin Authentication**: Only authenticated admins can manage slideshow
- **Audit Trail**: All changes are logged for tracking
- **File Cleanup**: Automatic file removal when images are deleted

## Troubleshooting

### Common Issues

1. **Images Not Loading**:
   - Check if `/api/slideshow/images` endpoint is accessible
   - Verify database connection
   - Check file permissions in upload directory

2. **Upload Failures**:
   - Ensure file is under 5MB
   - Check file format (JPG, PNG, GIF only)
   - Verify admin authentication

3. **Slideshow Not Working**:
   - Check browser console for JavaScript errors
   - Verify `dynamic-slideshow.js` is loaded
   - Ensure slideshow container exists

### Debug Mode

Enable debug logging by checking browser console for:
- API response messages
- Image loading status
- Transition timing information

## Future Enhancements

- **Image Cropping**: Built-in image editing tools
- **Bulk Operations**: Upload/delete multiple images at once
- **Scheduled Display**: Show images at specific times
- **A/B Testing**: Test different image combinations
- **Analytics**: Track slideshow performance metrics

## Support

For technical support or feature requests, contact the development team or create an issue in the project repository.
