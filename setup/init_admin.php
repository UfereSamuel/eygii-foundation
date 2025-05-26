<?php
/**
 * EYGII Admin Initialization Script
 * Run this script once to set up the admin user and test the system
 */

require_once '../config/database.php';

echo "<h2>EYGII Admin Initialization</h2>";

try {
    $db = Database::getInstance();
    
    // Test database connection
    echo "<p>✅ Database connection successful</p>";
    
    // Check if admin_users table exists
    $tables = $db->fetchAll("SHOW TABLES LIKE 'admin_users'");
    if (empty($tables)) {
        echo "<p>❌ admin_users table not found. Please run the database.sql script first.</p>";
        exit;
    }
    
    // Check if admin user already exists
    $existing_admin = $db->fetch("SELECT * FROM admin_users WHERE username = 'admin'");
    
    if ($existing_admin) {
        echo "<p>⚠️ Admin user already exists</p>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p>Please use the existing password or reset it manually in the database.</p>";
    } else {
        // Create default admin user
        $admin_data = [
            'username' => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'full_name' => 'EYGII Administrator',
            'email' => 'eygii2017@gmail.com',
            'role' => 'super_admin',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $admin_id = $db->insert('admin_users', $admin_data);
        
        if ($admin_id) {
            echo "<p>✅ Default admin user created successfully!</p>";
            echo "<p><strong>Username:</strong> admin</p>";
            echo "<p><strong>Password:</strong> admin123</p>";
            echo "<p><strong>⚠️ IMPORTANT:</strong> Please change this password immediately after first login!</p>";
        } else {
            echo "<p>❌ Failed to create admin user</p>";
        }
    }
    
    // Test email configuration
    echo "<h3>Email Configuration Test</h3>";
    require_once '../config/email.php';
    
    $emailService = new EmailService();
    echo "<p>✅ Email service initialized</p>";
    
    // Check database tables
    echo "<h3>Database Tables Status</h3>";
    $required_tables = [
        'admin_users',
        'contact_submissions', 
        'donation_inquiries',
        'programs',
        'news',
        'volunteers',
        'newsletter_subscribers',
        'site_settings'
    ];
    
    foreach ($required_tables as $table) {
        $exists = $db->fetchAll("SHOW TABLES LIKE '$table'");
        if (!empty($exists)) {
            $count = $db->fetch("SELECT COUNT(*) as count FROM $table")['count'];
            echo "<p>✅ $table ($count records)</p>";
        } else {
            echo "<p>❌ $table (missing)</p>";
        }
    }
    
    // Insert sample data if tables are empty
    echo "<h3>Sample Data</h3>";
    
    // Sample programs
    $programs_count = $db->fetch("SELECT COUNT(*) as count FROM programs")['count'];
    if ($programs_count == 0) {
        $sample_programs = [
            [
                'title' => 'Leadership Training Program',
                'slug' => 'leadership-training',
                'description' => 'A comprehensive 12-week program designed to develop essential leadership skills in young people aged 16-30.',
                'content' => 'This program covers communication skills, team building, project management, and ethical decision-making.',
                'start_date' => date('Y-m-d', strtotime('+1 month')),
                'end_date' => date('Y-m-d', strtotime('+4 months')),
                'location' => 'EYGII Training Center, Ibadan',
                'max_participants' => 30,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'title' => 'Community Service Initiative',
                'slug' => 'community-service',
                'description' => 'Monthly community outreach programs addressing local needs through organized service projects.',
                'content' => 'Volunteers work together to create tangible positive impact in communities through various service activities.',
                'location' => 'Various locations in Ibadan',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($sample_programs as $program) {
            $db->insert('programs', $program);
        }
        echo "<p>✅ Sample programs added</p>";
    }
    
    // Sample news
    $news_count = $db->fetch("SELECT COUNT(*) as count FROM news")['count'];
    if ($news_count == 0) {
        $sample_news = [
            [
                'title' => 'EYGII Launches New Youth Leadership Initiative',
                'slug' => 'youth-leadership-initiative',
                'content' => 'EYGII is proud to announce the launch of our comprehensive youth leadership development program, designed to empower young people with the skills and knowledge needed to become effective leaders in their communities.',
                'excerpt' => 'New leadership program launched to empower young people in communities.',
                'category' => 'programs',
                'status' => 'published',
                'published_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'title' => 'Community Outreach Reaches 500 Families',
                'slug' => 'community-outreach-success',
                'content' => 'Our recent community service initiative successfully reached over 500 families in underserved communities across Ibadan, providing essential supplies and support.',
                'excerpt' => 'Monthly outreach program makes significant impact in local communities.',
                'category' => 'community',
                'status' => 'published',
                'published_at' => date('Y-m-d H:i:s', strtotime('-1 week')),
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($sample_news as $article) {
            $db->insert('news', $article);
        }
        echo "<p>✅ Sample news articles added</p>";
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p>🎉 Your EYGII website is ready to use!</p>";
    echo "<p><a href='../admin/index.php' style='background: #1e3a8a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Panel</a></p>";
    echo "<p><a href='../index.php' style='background: #f59e0b; color: #1f2937; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>View Website</a></p>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li>Login to admin panel and change the default password</li>";
    echo "<li>Configure email settings in config/email.php</li>";
    echo "<li>Add your organization's logo and images</li>";
    echo "<li>Customize content and add your programs</li>";
    echo "<li>Test contact and donation forms</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration and ensure the database exists.</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    line-height: 1.6;
}
h2, h3 {
    color: #1e3a8a;
}
p {
    margin: 10px 0;
}
ul {
    margin: 20px 0;
}
li {
    margin: 5px 0;
}
</style> 