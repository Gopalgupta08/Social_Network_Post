# File Structure


** social_network_project/
├── config/database.php          # Database configuration
├── classes/                     # OOP classes
│   ├── User.php                # User management
│   └── Post.php                # Post management
├── includes/functions.php       # Helper functions
├── pages/                      # Main application pages
│   ├── signup.php              # User registration
│   ├── login.php               # User authentication
│   ├── profile.php             # Profile with posts
│   └── logout.php              # Session termination
├── ajax/                       # AJAX handlers
│   ├── add_post.php            # Add new posts
│   ├── delete_post.php         # Delete posts
│   ├── update_profile.php      # Update profile
│   └── like_dislike.php        # Handle likes/dislikes
├── assets/
│   ├── css/style.css           # Complete styling
│   ├── js/script.js            # JavaScript functionality
│   └── uploads/                # File uploads
└── index.php                   # Entry point  
**
