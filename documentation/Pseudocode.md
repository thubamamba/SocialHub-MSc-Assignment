# SynCNet Page Functionality Pseudocode

## 1. Home Page (index.php)

```
BEGIN HomePage
    LOAD configuration and database connection
    INCLUDE header and footer components
    
    IF request method is POST AND login form submitted THEN
        GET username/email and password from form
        SANITIZE input data
        QUERY database for user with matching credentials
        
        IF user exists AND password verification successful THEN
            CREATE user session with ID, username, and user level
            DISPLAY success message via JavaScript alert
        ELSE
            DISPLAY error message via JavaScript alert
        END IF
    END IF
    
    IF logout parameter present THEN
        DESTROY user session
        REDIRECT to home page
    END IF
    
    GET session messages (success/error)
    CLEAR session messages after retrieval
    
    QUERY database for all posts with user information and interaction counts:
        - JOIN posts with users table
        - COUNT likes for each post
        - COUNT comments for each post  
        - CHECK if current user has liked each post (if logged in)
        - ORDER by creation date descending
    
    GET current user data if logged in
    
    DISPLAY page structure:
        SHOW carousel with featured images for all users
        SHOW posts feed with all retrieved posts for all users
        SHOW search bar in navigation (available to all users)
        
        IF user is logged in THEN
            SHOW left sidebar with user profile summary
            SHOW post creation form in main feed
            SHOW interactive like/comment buttons on posts
            SHOW right sidebar with suggested users
        ELSE
            HIDE user-specific elements (post creation, sidebars)
            SHOW login modal and register link
            SHOW posts in read-only mode
        END IF
        
    FOR each post in posts feed:
        DISPLAY post content and image if present
        SHOW post metadata (author, date, likes, comments)
        
        IF user is logged in THEN
            SHOW like button with current state
            SHOW comment form and existing comments
            
            IF user owns post OR user is moderator THEN
                SHOW delete option
            END IF
        ELSE
            SHOW like/comment counts as read-only text
            DISPLAY comments but no interaction forms
        END IF
    END FOR
    
    INCLUDE JavaScript for:
        - Theme switching functionality (all users)
        - User search functionality (all users)
        - AJAX like/unlike operations (logged in only)
        - Comment adding/deleting (logged in only)
        - Post deletion with confirmation (logged in only)
END HomePage
```

## 2. Registration Page (register.php)

```
BEGIN RegistrationPage
    LOAD configuration and database connection
    
    IF user is already logged in THEN
        REDIRECT to home page
    END IF
    
    INITIALIZE errors array
    SET success flag to false
    
    IF request method is POST THEN
        GET form data:
            - username, email, password, confirm_password
            - user_level, bio, profile_picture file
        
        SANITIZE all text inputs
        
        VALIDATE input data:
            IF username length < 3 THEN ADD error "Username too short"
            IF email format invalid THEN ADD error "Invalid email"
            IF password length < 8 THEN ADD error "Password too short"
            IF passwords don't match THEN ADD error "Passwords don't match"
            IF user_level not in [1,2] THEN ADD error "Invalid user level"
        END VALIDATION
        
        IF no validation errors THEN
            CHECK if username or email already exists in database
            IF username/email exists THEN ADD error "Already exists"
        END IF
        
        HANDLE profile picture upload:
            IF file uploaded THEN
                VALIDATE file type (JPEG, PNG, GIF only)
                IF valid THEN
                    GENERATE unique filename
                    MOVE file to uploads directory
                ELSE
                    SET profile_picture to default
                    ADD error if invalid type
                END IF
            ELSE
                SET profile_picture to default
            END IF
        END HANDLE
        
        IF no errors THEN
            HASH password using secure algorithm
            INSERT new user into database
            CREATE user session (auto-login)
            SET success flag to true
            SCHEDULE redirect to home page after 2 seconds
        END IF
    END IF
    
    DISPLAY registration form with:
        - Profile picture preview with upload button
        - Username and email fields with validation
        - Password fields with strength indicator
        - User level selection (Regular/Moderator)
        - Bio text area with character counter
        - Terms and conditions checkbox
        
    IF errors exist THEN DISPLAY error messages
    IF success THEN DISPLAY success message and redirect script
    
    INCLUDE JavaScript for:
        - Image preview functionality
        - Password strength checking
        - Form validation
        - Character counting
        - Real-time validation feedback
END RegistrationPage
```

## 3. Profile Page (profile.php)

```
BEGIN ProfilePage
    LOAD configuration, header, and footer components
    
    GET user ID from URL parameter
    IF user ID missing THEN REDIRECT to home page
    
    QUERY database for user profile data
    IF user not found THEN REDIRECT to home page
    
    CHECK if viewing own profile (user_id matches session if logged in)
    
    QUERY database for user's posts with interaction counts:
        - GET all posts by this user
        - COUNT likes and comments for each post
        - CHECK if current viewer has liked posts (if logged in)
        - ORDER by creation date descending
    
    CALCULATE user statistics:
        - COUNT total posts by user
        - COUNT total likes received on user's posts  
        - COUNT total comments received on user's posts
    
    GET current logged-in user data (if any)
    
    DISPLAY profile information:
        - Profile picture 
        - Username and user level badge
        - Bio text if present
        - Join date
        - Statistics (posts, likes, comments)
        
        IF logged in AND own profile THEN
            SHOW "Edit Profile" button
            SHOW post creation form
        END IF
    
    DISPLAY user's posts:
        IF user has posts THEN
            FOR each post:
                SHOW post content and image
                SHOW post metadata
                
                IF viewer is logged in THEN
                    SHOW like button with current state
                    SHOW comments with toggle visibility
                    SHOW comment form
                    
                    IF viewer owns post OR viewer is moderator THEN
                        SHOW delete option
                    END IF
                ELSE
                    SHOW like/comment counts as read-only text
                    SHOW existing comments but no interaction forms
                END IF
            END FOR
        ELSE
            IF own profile THEN
                SHOW "Share your first post to get started!"
            ELSE
                SHOW "User hasn't shared any posts yet"
            END IF
        END IF
    
    IF logged in AND own profile THEN
        SHOW edit profile modal with:
            - Profile picture upload with preview
            - Bio text area
            - Form submission to update_profile.php
    END IF
    
    INCLUDE JavaScript for:
        - Post interactions (like, comment, delete) - logged in users only
        - Comment management - logged in users only
        - Image preview for profile updates - own profile only
        - Modal handling - all users
END ProfilePage
```

## 4. Admin Panel (admin.php)

```
BEGIN AdminPanel
    LOAD configuration and database connection
    
    IF user not logged in THEN
        SET error message "Please log in to access admin panel"
        REDIRECT to home page
    END IF
    
    GET current user data
    IF user not found OR user level != 2 THEN
        SET error message "No permission to access admin panel"
        REDIRECT to home page
    END IF
    
    SET current page identifier to 'admin'
    
    DISPLAY admin dashboard:
        - Welcome message for administrator
        - Admin control panel interface
        - Tools and management options placeholder
        - Navigation back to home page
        
    FUTURE FUNCTIONALITY (to be implemented):
        - User management (view, edit, suspend users)
        - Content moderation (review reported posts/comments)
        - System settings configuration
        - Analytics and statistics dashboard
        - Bulk content management tools
        
    INCLUDE standard navigation and footer
END AdminPanel
```

## 5. Post Creation Handler (create_post.php)

```
BEGIN CreatePost
    LOAD configuration and database connection
    
    IF user not logged in THEN REDIRECT to home page
    IF request method not POST THEN REDIRECT to home page
    
    GET user ID from session
    GET post content from form
    INITIALIZE errors array
    
    VALIDATE post content:
        IF content is empty THEN ADD error "Content cannot be empty"
        IF content length > 1000 THEN ADD error "Content too long"
    
    HANDLE image upload:
        IF image file uploaded THEN
            VALIDATE file:
                - CHECK file type (JPEG, PNG, GIF, WebP only)
                - CHECK file size (max 5MB)
                
            IF validation passes THEN
                GENERATE unique filename
                SET upload path
                CREATE upload directory if needed
                
                IF file move successful THEN
                    RESIZE image if too large (max 1200x1200)
                    SET image_url to filename
                ELSE
                    ADD error "Failed to upload image"
                    SET image_url to null
                END IF
            ELSE
                ADD errors for invalid file
            END IF
        END IF
    
    IF no errors THEN
        SANITIZE post content
        INSERT new post into database with user_id, content, image_url
        SET success message "Post shared successfully"
    ELSE
        IF image was uploaded but database failed THEN
            DELETE uploaded image file
        END IF
        SET error messages in session
    END IF
    
    REDIRECT to referring page or home page
END CreatePost

FUNCTION ResizeImage(file_path, max_width, max_height):
    GET image information and type
    IF image too large THEN
        CALCULATE new dimensions maintaining aspect ratio
        CREATE resized image with proper transparency handling
        SAVE resized image over original
        CLEAN up memory
    END IF
END FUNCTION
```

## 6. Profile Update Handler (update_profile.php)

```
BEGIN UpdateProfile
    LOAD configuration and database connection
    
    IF user not logged in THEN REDIRECT to home page
    IF request method not POST THEN REDIRECT to profile page
    
    GET user ID from session
    GET bio from form
    INITIALIZE errors array
    
    VALIDATE bio:
        IF bio length > 500 THEN ADD error "Bio too long"
    
    GET current user profile data
    SET current profile picture as default
    
    HANDLE profile picture upload:
        IF new profile picture uploaded THEN
            VALIDATE file:
                - CHECK file type (JPEG, PNG, GIF, WebP only)
                - CHECK file size (max 2MB)
                
            IF validation passes THEN
                GENERATE unique filename with user ID
                SET upload path
                CREATE upload directory if needed
                
                IF file move successful THEN
                    RESIZE and crop to square (300x300)
                    DELETE old profile picture if not default
                    SET new profile picture filename
                ELSE
                    ADD error "Failed to upload profile picture"
                END IF
            ELSE
                ADD errors for invalid file
            END IF
        END IF
    
    IF no errors THEN
        SANITIZE bio content
        UPDATE user record with new bio and profile picture
        SET success message "Profile updated successfully"
    ELSE
        SET error messages in session
    END IF
    
    REDIRECT to profile page
END UpdateProfile

FUNCTION ResizeProfilePicture(file_path, size):
    GET image information
    CALCULATE crop dimensions for square aspect ratio
    CREATE square canvas with specified size
    HANDLE transparency for PNG/GIF files
    CROP and resize image to square
    SAVE processed image
    CLEAN up memory
END FUNCTION
```

## 7. API Endpoints Pseudocode

### toggle_like.php
```
BEGIN ToggleLike
    SET content type to JSON
    IF user not logged in THEN RETURN error JSON
    IF request not POST THEN RETURN error JSON
    
    GET post_id from JSON input
    VALIDATE post_id exists
    
    CHECK if post exists in database
    GET user_id from session
    
    CHECK if user already liked this post
    IF already liked THEN
        DELETE like record
        DECREMENT likes count on post
        SET liked status to false
    ELSE
        INSERT like record
        INCREMENT likes count on post  
        SET liked status to true
    END IF
    
    GET updated likes count
    RETURN JSON with success, liked status, and count
END ToggleLike
```

### add_comment.php
```
BEGIN AddComment
    SET content type to JSON
    IF user not logged in THEN RETURN error JSON
    IF request not POST THEN RETURN error JSON
    
    GET post_id and content from JSON input
    VALIDATE inputs (not empty, length limits)
    
    CHECK if post exists
    SANITIZE comment content
    
    INSERT comment into database
    GET comment data with user information
    CHECK if current user can delete this comment
    
    RETURN JSON with success and formatted comment data
END AddComment
```

### delete_post.php
```
BEGIN DeletePost
    SET content type to JSON
    IF user not logged in THEN RETURN error JSON
    IF request not POST THEN RETURN error JSON
    
    GET post_id from JSON input
    GET user_id and user_level from session
    
    GET post information including owner and image
    CHECK if user can delete (owner or moderator)
    
    BEGIN database transaction
    DELETE related likes
    DELETE related comments  
    DELETE the post
    
    IF deletion successful THEN
        DELETE associated image file if exists
        COMMIT transaction
        RETURN success JSON
    ELSE
        ROLLBACK transaction
        RETURN error JSON
    END IF
END DeletePost
```

### search_users.php
```
BEGIN SearchUsers
    SET content type to JSON
    IF request not GET THEN RETURN error JSON
    
    GET search query from URL parameter
    VALIDATE query (not empty, minimum length)
    
    SEARCH database for users matching query in username or bio
    ORDER results by relevance (exact username matches first)
    LIMIT results to 20 users
    
    FORMAT results with user information and safety
    RETURN JSON with success, users array, and count
    
    NOTE: This endpoint is available to all users (no login required)
    Allows guest users to discover and find profiles to view
END SearchUsers
```

---

## Summary

This pseudocode documentation covers the complete functionality of each page and API endpoint in the SynCNet social media platform, demonstrating broadly the following key features:

- **User authentication and authorization**
- **Content creation and management**
- **Real-time interactions (likes, comments)**
- **File upload and image processing**
- **Database operations and validation**
- **Security measures and error handling**
- **Responsive user interface considerations**
