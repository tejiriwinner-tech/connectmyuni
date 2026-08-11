# Admin Panel Quick Start Guide

## What's New

Your Connect MyUni website now includes a professional admin panel for managing events and website content!

## Access the Admin Panel

### Admin Dashboard

```
http://localhost/myuni/admin/
```

### File Locations

- **Admin Dashboard**: `/admin/index.php`
- **Post Event Form**: `/admin/post-event.php`
- **Event Listing Page**: `/events.php`

## Quick Steps to Post Your First Event

### 1. Navigate to Admin Panel

Open your browser and go to: `http://localhost/myuni/admin/`

You'll see a dashboard with options to:

- 📅 Post Event (NEW)
- 📋 View Events
- 📊 Statistics (Coming Soon)
- ⚙️ Settings (Coming Soon)
- 👥 Users (Coming Soon)
- 🌐 View Website

### 2. Click "Post Event"

This takes you to the event posting form with the following fields:

```
┌──────────────────────────────┐
│  POST NEW EVENT              │
├──────────────────────────────┤
│                              │
│ Event Title *                │
│ [Enter event title]          │
│                              │
│ Event Category *             │
│ [Webinar / Workshop / Video] │
│                              │
│ Event Date *                 │
│ [YYYY-MM-DD]                 │
│                              │
│ Event Description *          │
│ [Detailed description...]    │
│                              │
│ Image URL                    │
│ [asset/event_placeholder]    │
│                              │
│        [POST EVENT]          │
└──────────────────────────────┘
```

### 3. Fill in Event Details

**Example: IELTS Workshop**

```
Event Title:    IELTS Preparation Masterclass
Category:       Workshop
Date:           2024-09-15
Description:    Join our intensive 4-week IELTS preparation workshop.
                Learn proven strategies for Reading, Writing, Speaking,
                and Listening with expert instructors.
Image URL:      asset/event_placeholder.jpg
```

### 4. Click "POST EVENT"

✅ Success! Your event is now live and visible on:

- Homepage (News & Events preview section)
- Full Events page (`/events.php`)
- Filtered by category

## Where Your Events Appear

### Homepage Preview

The Events section shows a glimpse of your latest events with a "VIEW ALL EVENTS" button.

**Location**: Between Approach section and Footer

### Events Page

Full events listing with filtering options:

- All Events
- Webinars
- Workshops
- Videos

**URL**: `/events.php`

## Event Categories Explained

### 🎥 Webinar

- Live or on-demand online sessions
- Typically interactive with Q&A
- Good for launching new courses or services
- Example: "Navigating Global Education Webinar"

### 👨‍🏫 Workshop

- Interactive training sessions
- Usually hands-on practice
- Limited duration (hours to days)
- Example: "IELTS Preparation Masterclass"

### 📹 Video

- Pre-recorded content
- Self-paced learning
- Series or compilations
- Example: "Career Development Video Series"

## Tips for Creating Engaging Events

### Titles

✅ **Good**: "IELTS Preparation Masterclass with Expert Trainers"
❌ **Bad**: "Event 1"

### Descriptions

✅ **Good**: "Learn proven strategies for all four IELTS components. Expert instructors with 10+ years experience will guide you through practice tests, tips, and exam strategies."
❌ **Bad**: "IELTS workshop"

### Dates

- Use future dates so events appear relevant
- Format: YYYY-MM-DD (e.g., 2024-09-15)
- Consider multiple events to keep content fresh

### Images

- Use professional, relevant images
- Aspect ratio: 16:9 or 4:3 works best
- Size: 500-2000px width for best quality
- Default placeholder loads but custom images look better

## Event Display Example

Each event shows as a professional card with:

```
╔═══════════════════════════════════╗
║ [EVENT IMAGE]      [WEBINAR]      ║
║ Event Title Here                  ║
║ 📅 September 15, 2024            ║
║                                   ║
║ This is the description that      ║
║ appears on the event card.        ║
║ It provides key details about     ║
║ the event topic and benefits.     ║
║                                   ║
║           [LEARN MORE]            ║
╚═══════════════════════════════════╝
```

## Managing Your Events

### View All Events

Click "View Events" on the dashboard to see all posted events.

### Filter Events

On the Events page, use button filters to show specific types:

- **All Events** - Show everything
- **Webinars** - Only webinars
- **Workshops** - Only workshops
- **Videos** - Only videos

### Edit/Delete Events (Coming Soon)

Future versions will allow:

- Edit existing events
- Change dates or descriptions
- Delete outdated events
- Bulk operations

## Important Notes

### Current System

✅ Events post instantly
✅ Auto-appear on homepage and events page
✅ Filter by category works immediately
✅ Responsive design (works on mobile, tablet, desktop)

⚠️ Currently uses simple data structure
⚠️ No authentication required (development mode)
⚠️ No database persistence (resets with code changes)

### Before Going Live

You should:

1. Set up MySQL database for event storage
2. Implement admin login system
3. Add event editing and deletion features
4. Enable image file uploads
5. Create email notification system
6. Add event registration capability

See `/admin/README.md` for detailed implementation guide.

## Frequently Asked Questions

### Q: How long does it take for events to appear?

**A:** Instantly! Posted events show up immediately on the homepage and events page.

### Q: Can I post events for past dates?

**A:** Yes, but it's better to use future dates so events appear current and relevant to visitors.

### Q: What image formats are supported?

**A:** Common formats: JPG, PNG, WebP, GIF. Use JPG for best compatibility.

### Q: Can I schedule events to post later?

**A:** Not in current version. Coming in future update with database integration.

### Q: How many events can I post?

**A:** No limit! Post as many as needed. Keep them organized and relevant.

### Q: Will events appear in search results?

**A:** Only if you have a search feature implemented. Coming in future versions.

### Q: Can I embed videos directly?

**A:** Currently shows images. Future versions will support embedded video players.

### Q: How do I advertise events?

**A:** Use "View All Events" button or direct link: `/events.php`

## Support Resources

### Files to Reference

- `/admin/README.md` - Complete admin documentation
- `/events.php` - Events page with all your posted events
- `/index.php` - Homepage with events preview
- `/style.css` - Design styling and colors

### Browser Console

Press `F12` → Console tab to check for any errors

### Test in Different Browsers

- Chrome
- Firefox
- Safari
- Edge
- Mobile browsers

## Next Steps

1. ✅ Post your first event
2. ✅ View it on the homepage and events page
3. ✅ Test filtering by category
4. ✅ Test responsive design on mobile
5. ⏭️ Plan upcoming webinars, workshops, videos
6. ⏭️ Gather user feedback
7. ⏭️ Consider database integration for production

## Contact & Support

For questions or technical support:

- Check `/admin/README.md` for detailed documentation
- Review browser console for error messages
- Verify event data format: title, category, date, description

---

**Ready to post your first event?**

👉 Go to: `http://localhost/myuni/admin/`

Enjoy managing your educational consultancy website! 🎉
