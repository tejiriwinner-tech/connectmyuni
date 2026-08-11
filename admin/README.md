# Admin Dashboard Guide - Connect MyUni

## Overview

The Admin Dashboard provides a centralized interface for managing website content, posting events, and maintaining the Connect MyUni educational consultancy website.

## Accessing the Admin Dashboard

### URL

```
http://localhost/myuni/admin/
```

### Current Features

- **Post Event** - Create and publish new webinars, workshops, and videos
- **View Events** - Browse all posted events
- **View Website** - Quick access to the live website

## Features & Usage

### 1. Post Event (`post-event.php`)

Create and publish educational events, webinars, workshops, and video content.

#### How to Use:

1. Navigate to Admin Dashboard → **Post Event**
2. Fill in the following information:
   - **Event Title** (Required) - Clear, engaging title (e.g., "IELTS Preparation Masterclass")
   - **Category** (Required) - Select from:
     - Webinar - Live or recorded online sessions
     - Workshop - Interactive training sessions
     - Video - Pre-recorded educational content
   - **Date** (Required) - Pick the event date
   - **Description** (Required) - Detailed description of what attendees will learn
   - **Image URL** (Optional) - Path to event image (default: asset/event_placeholder.jpg)
3. Click **"Post Event"** button
4. Event will appear on:
   - Homepage (News & Events preview section)
   - Full Events page (`events.php`)
   - Can be filtered by category

#### Event Form Fields:

```
Title:        Text input (max suggested: 100 characters)
Category:     Dropdown (webinar, workshop, video)
Date:         Date picker (YYYY-MM-DD format)
Description:  Textarea (detailed event information)
Image:        Image URL/path (default provided)
```

#### Example Event Data:

```
Title: IELTS Preparation Masterclass
Category: workshop
Date: 2024-08-20
Description: Join our intensive 4-week IELTS preparation workshop. Learn proven strategies for all four components (Reading, Writing, Speaking, Listening). Expert instructors with 10+ years of experience.
Image: asset/event_placeholder.jpg
```

### 2. View Events & Filtering

The Events page (`events.php`) displays all posted events with category filtering.

#### Filtering Options:

- **All Events** - Shows all posted events
- **Webinars** - Live/online sessions and webinars
- **Workshops** - Interactive training sessions
- **Videos** - Pre-recorded content and series

#### Event Card Display:

Each event shows:

- Event image
- Category badge (color-coded)
- Title
- Date with calendar icon
- Description excerpt
- "Learn More" button

### 3. Homepage Integration

Events automatically appear in two places:

#### a) News & Events Preview Section

- Shows 1-3 sample events
- Located between Approach section and Footer
- Includes "VIEW ALL EVENTS" button linking to events page
- Responsive grid layout

#### b) Full Events Page

- Complete event listing with filtering
- All posted events visible
- Category-based filtering
- Professional card design with hover effects

## Current Architecture

### File Structure

```
/myuni/
├── admin/
│   ├── index.php          (Admin Dashboard)
│   ├── post-event.php     (Event posting form)
│   └── README.md          (This file)
├── events.php             (Full events page with filtering)
├── index.php              (Homepage with events preview)
├── header.php
├── footer.php
├── style.css
└── asset/
    └── event_placeholder.jpg
```

### Data Structure

Current events are stored in a JavaScript array in `events.php`:

```javascript
const events = [
  {
    id: 1,
    title: "Event Title",
    category: "webinar|workshop|video",
    date: "YYYY-MM-DD",
    description: "Event description",
    image: "path/to/image.jpg",
    badge: "WEBINAR|WORKSHOP|VIDEO",
  },
  // ... more events
];
```

## Future Enhancements

### Planned Features

1. **Database Integration** - Store events in MySQL/PostgreSQL
2. **Event Detail Pages** - Individual pages for each event
3. **User Authentication** - Secure admin login system
4. **Event Editing** - Modify or delete existing events
5. **Image Upload** - Upload files instead of linking URLs
6. **Email Notifications** - Notify subscribers about new events
7. **Event Registration** - Allow users to register for events
8. **Analytics Dashboard** - Track event engagement metrics
9. **Content Management** - Edit other website sections
10. **User Management** - Manage multiple admin accounts

## Implementation Guide for Database Integration

### Step 1: Create Database Table

```sql
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category ENUM('webinar', 'workshop', 'video') NOT NULL,
    date DATE NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) DEFAULT 'asset/event_placeholder.jpg',
    badge VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Step 2: Update post-event.php

Replace the form submission handling with database insert:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input...

    // Insert into database
    $stmt = $mysqli->prepare("INSERT INTO events (title, category, date, description, image, badge) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $category, $date, $description, $image, $badge);

    if ($stmt->execute()) {
        $success = true;
        $message = 'Event posted successfully!';
    }
}
```

### Step 3: Update events.php

Fetch events from database instead of static array:

```php
// Fetch events from database
$result = $mysqli->query("SELECT * FROM events ORDER BY date DESC");
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
```

## Security Considerations

### Current Status

⚠️ **Development/Testing Only** - No authentication required

### Before Going Live

1. **Implement Authentication**
   - Admin login system with password hashing
   - Session management
   - Login form and validation

2. **Validate Input**
   - Sanitize all form inputs
   - Validate date formats
   - Check file uploads

3. **Prevent SQL Injection**
   - Use prepared statements
   - Parameterized queries

4. **Protect Admin Area**
   - Require login to access `/admin/` directory
   - Use `.htaccess` or PHP session checks
   - Implement CSRF tokens on forms

### Security Implementation Example

```php
<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Validate CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid request');
    }
}
?>
```

## Styling & Design

### Color Scheme

- **Primary**: #0052CC (Deep Blue)
- **Secondary**: #CC3333 (Red)
- **Light Background**: #f5f5f5
- **Text**: #333333, #666666, #999999

### CSS Classes Used

- `.admin-section` - Main admin container
- `.admin-card` - Form card styling
- `.admin-title` - Section heading
- `.form-group` - Form field wrapper
- `.btn-submit` - Submit button
- `.success-message` - Success notification
- `.error-message` - Error notification

### Responsive Design

- Mobile: < 768px
- Tablet: 768px - 992px
- Desktop: > 992px

All admin pages are fully responsive.

## Troubleshooting

### Form Not Submitting?

1. Check browser console for JavaScript errors
2. Verify form field names match PHP POST variables
3. Ensure form method is POST and action is blank (submits to self)

### Images Not Displaying?

1. Check image path is correct
2. Verify image file exists in `asset/` folder
3. Use full path or relative path consistently

### Events Not Appearing?

1. Check JavaScript renders events (open browser console)
2. Verify date format is YYYY-MM-DD
3. Check category matches filter options (webinar, workshop, video)

### CSS Not Loading?

1. Check cache-busting parameter: `style.css?v=<?php echo time(); ?>`
2. Clear browser cache (Ctrl+Shift+Delete)
3. Verify file path is correct

## Support & Development

### Next Steps for Enhancement

1. Set up local development environment
2. Install XAMPP/WAMP with MySQL
3. Create database and import tables
4. Update connection strings in PHP files
5. Test form submissions with database
6. Implement user authentication
7. Add more admin features as needed

### Testing the Admin System

1. Post a new event with test data
2. Navigate to events.php and verify it appears
3. Check homepage for preview
4. Test category filtering
5. Verify responsive design on mobile

### Performance Tips

- Use image optimization tools before uploading
- Implement caching for events list
- Optimize database queries with indexes
- Lazy load images on events page

---

**Last Updated**: 2024
**Version**: 1.0 (Initial Release)
**Status**: Development/Testing

For assistance or feature requests, please contact the development team.
