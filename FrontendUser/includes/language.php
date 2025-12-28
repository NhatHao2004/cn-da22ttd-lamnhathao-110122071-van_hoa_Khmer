<?php
/**
 * Language System - Đa ngôn ngữ Việt/Khmer
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'vi';
}

// Switch language
if (isset($_GET['lang']) && in_array($_GET['lang'], ['vi', 'km'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'];

// Language strings
$translations = [
    'vi' => [
        // General
        'site_name' => 'Văn hóa Khmer Nam Bộ',
        'home' => 'Trang chủ',
        'search' => 'Tìm kiếm',
        'login' => 'Đăng nhập',
        'register' => 'Đăng ký',
        'logout' => 'Đăng xuất',
        'profile' => 'Trang cá nhân',
        'settings' => 'Cài đặt',
        
        // Navigation
        'nav_culture' => 'Văn hóa',
        'nav_temples' => 'Chùa Khmer',
        'nav_festivals' => 'Lễ hội',
        'nav_learn' => 'Học tiếng Khmer',
        'nav_stories' => 'Truyện dân gian',
        'nav_map' => 'Bản đồ di sản',
        'nav_forum' => 'Diễn đàn',
        
        // Homepage
        'hero_title' => 'Khám phá Văn hóa Khmer Nam Bộ',
        'hero_subtitle' => 'Nền tảng số hóa và bảo tồn di sản văn hóa Khmer',
        'explore_now' => 'Khám phá ngay',
        'learn_more' => 'Tìm hiểu thêm',
        
        // Stats
        'stat_temples' => 'Ngôi chùa',
        'stat_festivals' => 'Lễ hội',
        'stat_lessons' => 'Bài học',
        'stat_users' => 'Người dùng',
        
        // Auth
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'confirm_password' => 'Xác nhận mật khẩu',
        'full_name' => 'Họ và tên',
        'remember_me' => 'Ghi nhớ đăng nhập',
        'forgot_password' => 'Quên mật khẩu?',
        'no_account' => 'Chưa có tài khoản?',
        'have_account' => 'Đã có tài khoản?',
        
        // Common
        'read_more' => 'Đọc thêm',
        'view_all' => 'Xem tất cả',
        'loading' => 'Đang tải...',
        'no_results' => 'Không có kết quả',
        'no_results_desc' => 'Không có bài viết nào phù hợp với tiêu chí tìm kiếm của bạn. Hãy thử tìm kiếm với từ khóa khác.',
        'save' => 'Lưu',
        'cancel' => 'Hủy',
        'delete' => 'Xóa',
        'edit' => 'Sửa',
        'share' => 'Chia sẻ',
        'like' => 'Thích',
        'comment' => 'Bình luận',
        'bookmark' => 'Lưu lại',
        'featured' => 'Nổi bật',
        'articles' => 'Bài viết',
        'total_views' => 'Lượt xem',
        'categories' => 'Danh mục',
        'all_articles' => 'Tất cả bài viết',
        'showing' => 'Hiển thị',
        'view_all_articles' => 'Xem tất cả bài viết',
        'reset' => 'Đặt lại',
        'search_article' => 'Tìm bài viết...',
        'saved_articles' => 'Bài viết đã lưu',
        'no_saved_articles' => 'Chưa có bài viết nào được lưu',
        
        // Homepage sections
        'feature_section_title' => 'Khám phá nền văn hóa Khmer',
        'feature_section_subtitle' => 'Những tính năng nổi bật giúp bạn tìm hiểu và bảo tồn văn hóa',
        'feature_culture_title' => 'Văn hóa Khmer',
        'feature_culture_desc' => 'Khám phá lịch sử, phong tục tập quán, nghệ thuật truyền thống và đời sống tinh thần phong phú của cộng đồng người Khmer Nam Bộ.',
        'feature_temple_title' => 'Chùa Khmer',
        'feature_temple_desc' => 'Tìm hiểu kiến trúc độc đáo, ý nghĩa tâm linh sâu sắc và vai trò quan trọng của các ngôi chùa trong đời sống cộng đồng Khmer.',
        'feature_festival_title' => 'Lễ hội truyền thống',
        'feature_festival_desc' => 'Cập nhật các lễ hội đặc sắc như Chol Chnam Thmay, Ok Om Bok, Sene Dolta và nhiều sự kiện văn hóa hấp dẫn khác.',
        'feature_learn_title' => 'Học tiếng Khmer',
        'feature_learn_desc' => 'Bài học tương tác với từ vựng phong phú, ngữ pháp dễ hiểu, phát âm chuẩn và bài tập thực hành đa dạng.',
        'feature_story_title' => 'Truyện dân gian',
        'feature_story_desc' => 'Kho tàng truyện cổ tích, thần thoại và truyền thuyết Khmer được sưu tầm công phu, mang đậm giá trị văn hóa truyền thống.',
        'feature_map_title' => 'Bản đồ di sản',
        'feature_map_desc' => 'Khám phá các địa điểm văn hóa, di tích lịch sử trên bản đồ tương tác với thông tin chi tiết và hình ảnh sinh động.',
        
        // Featured sections
        'featured_culture' => 'Văn hóa nổi bật',
        'featured_culture_desc' => 'Những bài viết được quan tâm nhất',
        'featured_temples' => 'Chùa Khmer tiêu biểu',
        'featured_temples_desc' => 'Những ngôi chùa mang đậm bản sắc văn hóa',
        'upcoming_festivals' => 'Lễ hội sắp diễn ra',
        'upcoming_festivals_desc' => 'Đừng bỏ lỡ những sự kiện văn hóa đặc sắc',
        'view_detail' => 'Xem chi tiết',
        
        // CTA section
        'cta_title' => 'Bắt đầu hành trình khám phá',
        'cta_desc' => 'Đăng ký tài khoản miễn phí để lưu bài học, theo dõi tiến trình và nhận huy hiệu thành tích.',
        'register_now' => 'Đăng ký ngay',
        'continue_learning' => 'Tiếp tục học',
        
        // Culture category
        'culture' => 'Văn hóa',
        
        // Search page
        'search_placeholder' => 'Nhập từ khóa tìm kiếm...',
        'search_results_for' => 'Kết quả tìm kiếm cho',
        'found_results' => 'Tìm thấy',
        'results' => 'kết quả',
        'try_different_keyword' => 'Thử tìm kiếm với từ khóa khác',
        'search_content' => 'Tìm kiếm nội dung',
        'search_hint' => 'Nhập từ khóa để tìm kiếm văn hóa, chùa, lễ hội, truyện và bài học',
        'temple' => 'Chùa',
        'story' => 'Truyện',
        'lesson' => 'Bài học',
        
        // Detail pages
        'views' => 'lượt xem',
        'bookmarked' => 'Đã lưu',
        'related_articles' => 'Bài viết liên quan',
        'no_comments' => 'Chưa có bình luận nào.',
        'write_comment' => 'Viết bình luận...',
        'send_comment' => 'Gửi bình luận',
        'login_to_comment' => 'để bình luận.',
        'comment_pending' => 'Bình luận đang chờ duyệt!',
        'article_not_found' => 'Bài viết không tồn tại.',
        'link_copied' => 'Đã sao chép link!',
        
        // Footer
        'footer_desc' => 'Nền tảng số hóa và bảo tồn di sản văn hóa Khmer Nam Bộ. Khám phá, học hỏi và kết nối với văn hóa truyền thống.',
        'footer_explore' => 'Khám phá',
        'footer_learning' => 'Học tập',
        'footer_support' => 'Hỗ trợ',
        'footer_leaderboard' => 'Bảng xếp hạng',
        'footer_about' => 'Giới thiệu',
        'footer_contact' => 'Liên hệ',
        'footer_terms' => 'Điều khoản sử dụng',
        'footer_privacy' => 'Chính sách bảo mật',
        'footer_copyright' => 'Bản quyền',
        
        // Page titles & descriptions
        'culture_page_title' => 'Văn hóa Khmer',
        'culture_page_desc' => 'Khám phá nét đẹp văn hóa truyền thống của người Khmer Nam Bộ',
        'culture_page_subtitle' => 'Khám phá nét đẹp văn hóa truyền thống của người Khmer Nam Bộ qua các bài viết chuyên sâu',
        'temple_page_title' => 'Chùa Khmer',
        'temple_page_desc' => 'Khám phá kiến trúc độc đáo của các ngôi chùa Khmer Nam Bộ',
        'festival_page_title' => 'Lễ hội Khmer',
        'festival_page_desc' => 'Những lễ hội truyền thống đặc sắc của người Khmer Nam Bộ',
        
        // Filters & sorting
        'all_categories' => 'Tất cả danh mục',
        'all_provinces' => 'Tất cả tỉnh thành',
        'newest' => 'Mới nhất',
        'popular' => 'Phổ biến',
        'oldest' => 'Cũ nhất',
        'filter' => 'Lọc',
        'search_temple' => 'Tìm chùa...',
        'try_different' => 'Thử thay đổi từ khóa hoặc bộ lọc',
        'sort_newest' => 'Mới nhất',
        'sort_popular' => 'Phổ biến nhất',
        'sort_oldest' => 'Cũ nhất',
        'provinces' => 'Tỉnh thành',
        'all_temples' => 'Tất cả chùa',
        'grid_view' => 'Dạng lưới',
        'map_view' => 'Bản đồ',
        'all_festivals' => 'Tất cả lễ hội',
        'all_stories' => 'Tất cả truyện',
        'stories' => 'Truyện',
        'genres' => 'Thể loại',
        'levels' => 'Cấp độ',
        'address' => 'Địa chỉ',
        'province' => 'Tỉnh thành',
        'coordinates' => 'Tọa độ',
        'not_available' => 'Chưa cập nhật',
        'manage_account' => 'Quản lý thông tin tài khoản của bạn',
        'enter_phone' => 'Nhập số điện thoại',
        'birthday' => 'Ngày sinh',
        'gender' => 'Giới tính',
        'username' => 'Tên đăng nhập',
        'username_hint' => 'Chỉ chứa chữ cái, số và dấu gạch dưới',
        'auth_feature_1' => 'Khám phá văn hóa, phong tục người Khmer',
        'auth_feature_2' => 'Học tiếng Khmer với bài học tương tác',
        'auth_feature_3' => 'Bản đồ di sản văn hóa tương tác',
        'register_subtitle' => 'Tham gia cộng đồng học tập và khám phá văn hóa',
        'select_gender' => 'Chọn giới tính',
        'male' => 'Nam',
        'female' => 'Nữ',
        'other' => 'Khác',
        
        // Festival filters
        'all' => 'Tất cả',
        'upcoming' => 'Sắp diễn ra',
        'past' => 'Đã qua',
        'detail' => 'Chi tiết',
        
        // Learning page
        'learn_page_desc' => 'Học tiếng Khmer qua các bài học tương tác thú vị',
        'learning_progress' => 'Tiến độ học tập',
        'basic' => 'Cơ bản',
        'intermediate' => 'Trung cấp',
        'advanced' => 'Nâng cao',
        'lesson_count' => 'bài',
        'minutes' => 'phút',
        'completed' => 'Hoàn thành',
        'in_progress' => 'Đang học',
        'not_started' => 'Chưa học',
        'review' => 'Ôn tập',
        'start' => 'Bắt đầu',
        'no_lessons' => 'Chưa có bài học nào',
        'lessons_coming' => 'Các bài học sẽ sớm được cập nhật.',
        'leaderboard' => 'Bảng xếp hạng',
        
        // Stories page
        'stories_page_desc' => 'Kho tàng truyện cổ tích, thần thoại và truyền thuyết Khmer',
        'search_story' => 'Tìm truyện...',
        'all_genres' => 'Tất cả thể loại',
        
        // Map page
        'map_detail' => 'Chi tiết',
        
        // Settings page
        'personal_info' => 'Thông tin cá nhân',
        'change_avatar' => 'Đổi ảnh đại diện',
        'avatar_hint' => 'JPG, PNG tối đa 5MB',
        'phone' => 'Số điện thoại',
        'change_password' => 'Đổi mật khẩu',
        'current_password' => 'Mật khẩu hiện tại',
        'new_password' => 'Mật khẩu mới',
        'confirm_new_password' => 'Xác nhận mật khẩu mới',
        'language' => 'Ngôn ngữ',
        'danger_zone' => 'Vùng nguy hiểm',
        'danger_zone_desc' => 'Xóa tài khoản sẽ xóa vĩnh viễn tất cả dữ liệu của bạn.',
        'delete_account' => 'Xóa tài khoản',
        'update_success' => 'Cập nhật thông tin thành công!',
        'password_change_success' => 'Đổi mật khẩu thành công!',
        'avatar_update_success' => 'Cập nhật ảnh đại diện thành công!',
        'wrong_current_password' => 'Mật khẩu hiện tại không đúng.',
        'confirm_delete' => 'Bạn có chắc chắn muốn xóa tài khoản? Hành động này không thể hoàn tác.',
        'feature_developing' => 'Tính năng đang được phát triển.',
        
        // Leaderboard page
        'leaderboard_desc' => 'Những người học tập xuất sắc nhất',
        'your_rank' => 'Xếp hạng của bạn',
        'rank' => 'Hạng',
        'user' => 'Người dùng',
        'you' => 'Bạn',
        'how_to_earn' => 'Cách kiếm điểm',
        'complete_lesson' => 'Hoàn thành bài học',
        'read_story' => 'Đọc truyện',
        'comment_action' => 'Bình luận',
        'learning_streak' => 'Streak học tập',
        'bonus_points' => 'Bonus điểm',
        
        // Profile page
        'joined_on' => 'Tham gia từ',
        'points' => 'Điểm',
        'lessons' => 'Bài học',
        'badges' => 'Huy hiệu',
        'saved' => 'Đã lưu',
        'badges_earned' => 'Huy hiệu đạt được',
        'saved_content' => 'Nội dung đã lưu',
        'recent_activity' => 'Hoạt động gần đây',
        'no_activity' => 'Chưa có hoạt động nào.',
        'please_login_profile' => 'Vui lòng đăng nhập để xem trang cá nhân.',
        'invalid_session' => 'Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại.',
        'manage_your_account' => 'Quản lý thông tin cá nhân của bạn',
        'register_subtitle' => 'Tham gia cộng đồng học tập và khám phá văn hóa',
        
        // Auth pages
        'welcome_back' => 'Chào mừng bạn quay trở lại!',
        'create_account' => 'Tạo tài khoản để bắt đầu học',
        'or' => 'hoặc',
        'session_expired' => 'Phiên làm việc hết hạn. Vui lòng thử lại.',
        'fill_all_info' => 'Vui lòng nhập đầy đủ thông tin.',
        'invalid_email' => 'Email không hợp lệ.',
        'password_min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        'password_not_match' => 'Mật khẩu xác nhận không khớp.',
        'agree_terms' => 'Bạn phải đồng ý với điều khoản sử dụng.',
        'email_exists' => 'Email này đã được sử dụng.',
        'wrong_credentials' => 'Email hoặc mật khẩu không đúng.',
        'register_success' => 'Đăng ký thành công! Vui lòng đăng nhập.',
        'login_success' => 'Đăng nhập thành công!',
        'error_occurred' => 'Có lỗi xảy ra. Vui lòng thử lại.',
        'terms_agree' => 'Tôi đồng ý với',
        'terms_of_use' => 'Điều khoản sử dụng',
        'and' => 'và',
        'min_6_chars' => 'Ít nhất 6 ký tự',
        'retype_password' => 'Nhập lại mật khẩu',
        
        // Forum
        'forum_title' => 'Diễn đàn Cộng đồng',
        'forum_subtitle' => 'Nơi kết nối, chia sẻ kiến thức và thảo luận về văn hóa Khmer Nam Bộ',
        'topics' => 'Chủ đề',
        'posts' => 'Bài viết',
        'members' => 'Thành viên',
        'discussion_categories' => 'Danh mục thảo luận',
        'no_categories' => 'Chưa có danh mục',
        'forum_setup_msg' => 'Hệ thống diễn đàn đang được thiết lập. Vui lòng quay lại sau!',
        'create_new_topic' => 'Tạo chủ đề mới',
        'login_to_join' => 'Đăng nhập để tham gia',
        'latest_discussions' => 'Thảo luận mới nhất',
        'no_topics_yet' => 'Chưa có chủ đề nào',
        'active_members' => 'Thành viên tích cực',
        
        // Forum Category Page
        'forum' => 'Diễn đàn',
        'topic_list' => 'Danh sách chủ đề',
        'topic_count' => 'chủ đề',
        'page' => 'Trang',
        'pinned' => 'Ghim',
        'locked' => 'Khóa',
        'hot' => 'Hot',
        'new' => 'Mới',
        'replies' => 'Trả lời',
        'views_count' => 'lượt xem',
        'no_topics_in_category' => 'Chưa có chủ đề nào',
        'be_first_to_create' => 'Hãy là người đầu tiên tạo chủ đề trong danh mục này!',
        'prev' => 'Trước',
        'next' => 'Sau',
        'quick_stats' => 'Thống kê nhanh',
        'other_categories' => 'Danh mục khác',
        'back_to_forum' => 'Quay lại diễn đàn',
        'view_all_categories' => 'Xem tất cả danh mục',
        'no_replies' => 'Chưa trả lời',
    ],
    'km' => [
        // General
        'site_name' => 'វប្បធម៌ខ្មែរភាគខាងត្បូង',
        'home' => 'ទំព័រដើម',
        'search' => 'ស្វែងរក',
        'login' => 'ចូល',
        'register' => 'ចុះឈ្មោះ',
        'logout' => 'ចាកចេញ',
        'profile' => 'ប្រវត្តិរូប',
        'settings' => 'ការកំណត់',
        
        // Navigation
        'nav_culture' => 'វប្បធម៌',
        'nav_temples' => 'វត្តខ្មែរ',
        'nav_festivals' => 'ពិធីបុណ្យ',
        'nav_learn' => 'រៀនភាសាខ្មែរ',
        'nav_stories' => 'រឿងព្រេងនិទាន',
        'nav_map' => 'ផែនទីបេតិកភណ្ឌ',
        'nav_forum' => 'វេទិកា',
        
        // Homepage
        'hero_title' => 'ស្វែងយល់វប្បធម៌ខ្មែរភាគខាងត្បូង',
        'hero_subtitle' => 'វេទិកាឌីជីថលសម្រាប់អភិរក្សបេតិកភណ្ឌវប្បធម៌ខ្មែរ',
        'explore_now' => 'ស្វែងរកឥឡូវ',
        'learn_more' => 'ស្វែងយល់បន្ថែម',
        
        // Stats
        'stat_temples' => 'វត្ត',
        'stat_festivals' => 'ពិធីបុណ្យ',
        'stat_lessons' => 'មេរៀន',
        'stat_users' => 'អ្នកប្រើប្រាស់',
        
        // Auth
        'email' => 'អ៊ីមែល',
        'password' => 'ពាក្យសម្ងាត់',
        'confirm_password' => 'បញ្ជាក់ពាក្យសម្ងាត់',
        'full_name' => 'ឈ្មោះពេញ',
        'remember_me' => 'ចងចាំខ្ញុំ',
        'forgot_password' => 'ភ្លេចពាក្យសម្ងាត់?',
        'no_account' => 'មិនមានគណនី?',
        'have_account' => 'មានគណនីរួចហើយ?',
        
        // Common
        'read_more' => 'អានបន្ថែម',
        'view_all' => 'មើលទាំងអស់',
        'loading' => 'កំពុងផ្ទុក...',
        'no_results' => 'គ្មានលទ្ធផល',
        'no_results_desc' => 'គ្មានអត្ថបទណាមួយត្រូវនឹងលក្ខណៈវិនិច្ឆ័យស្វែងរករបស់អ្នកទេ។ សូមសាកល្បងស្វែងរកជាមួយពាក្យគន្លឹះផ្សេង។',
        'save' => 'រក្សាទុក',
        'cancel' => 'បោះបង់',
        'delete' => 'លុប',
        'edit' => 'កែសម្រួល',
        'share' => 'ចែករំលែក',
        'like' => 'ចូលចិត្ត',
        'comment' => 'មតិយោបល់',
        'bookmark' => 'រក្សាទុក',
        'featured' => 'ពិសេស',
        'articles' => 'អត្ថបទ',
        'total_views' => 'ការមើល',
        'categories' => 'ប្រភេទ',
        'all_articles' => 'អត្ថបទទាំងអស់',
        'showing' => 'បង្ហាញ',
        'view_all_articles' => 'មើលអត្ថបទទាំងអស់',
        'reset' => 'កំណត់ឡើងវិញ',
        'search_article' => 'ស្វែងរកអត្ថបទ...',
        'saved_articles' => 'អត្ថបទដែលបានរក្សាទុក',
        'no_saved_articles' => 'មិនមានអត្ថបទដែលបានរក្សាទុកទេ',
        
        // Homepage sections
        'feature_section_title' => 'ស្វែងយល់វប្បធម៌ខ្មែរ',
        'feature_section_subtitle' => 'មុខងារពិសេសជួយអ្នកស្វែងយល់និងអភិរក្សវប្បធម៌',
        'feature_culture_title' => 'វប្បធម៌ខ្មែរ',
        'feature_culture_desc' => 'ស្វែងយល់ប្រវត្តិសាស្រ្តរាប់ពាន់ឆ្នាំ ទំនៀមទម្លាប់ពិសេស សិល្បៈប្រពៃណី និងជីវិតខាងព្រលឹងដ៏សម្បូរបែបរបស់សហគមន៍ជនជាតិខ្មែរភាគខាងត្បូង។',
        'feature_temple_title' => 'វត្តខ្មែរ',
        'feature_temple_desc' => 'ស្វែងយល់អំពីស្ថាបត្យកម្មពិសេសដែលមានលក្ខណៈវប្បធម៌ អត្ថន័យខាងព្រលឹងជ្រាលជ្រៅ និងតួនាទីសំខាន់នៃវត្តក្នុងជីវិតសហគមន៍។',
        'feature_festival_title' => 'ពិធីបុណ្យប្រពៃណី',
        'feature_festival_desc' => 'ធ្វើបច្ចុប្បន្នភាពព័ត៌មានលម្អិតអំពីពិធីបុណ្យពិសេសដូចជា ចូលឆ្នាំថ្មី អកអំបុក សែនដូនតា និងព្រឹត្តិការណ៍វប្បធម៌គួរឱ្យចាប់អារម្មណ៍ជាច្រើនទៀត។',
        'feature_learn_title' => 'រៀនភាសាខ្មែរ',
        'feature_learn_desc' => 'មេរៀនអន្តរកម្មដែលបានរចនាយ៉ាងវិទ្យាសាស្រ្តជាមួយវាក្យសព្ទសម្បូរបែប វេយ្យាករណ៍ងាយយល់ ការបញ្ចេញសំឡេងត្រឹមត្រូវ និងលំហាត់អនុវត្តចម្រុះ។',
        'feature_story_title' => 'រឿងព្រេងនិទាន',
        'feature_story_desc' => 'ឃ្លាំងរឿងព្រេង ទេវកថា និងរឿងព្រេងនិទានខ្មែរដែលបានប្រមូល និងចងក្រងយ៉ាងល្អិតល្អន់ ផ្ទុកនូវតម្លៃវប្បធម៌ប្រពៃណី។',
        'feature_map_title' => 'ផែនទីបេតិកភណ្ឌ',
        'feature_map_desc' => 'ស្វែងរកទីតាំងវប្បធម៌ បេតិកភណ្ឌប្រវត្តិសាស្រ្ត និងទេសភាពល្បីល្បាញនៅលើផែនទីអន្តរកម្មជាមួយព័ត៌មានលម្អិត និងរូបភាពរស់រវើក។',
        
        // Featured sections
        'featured_culture' => 'វប្បធម៌ពិសេស',
        'featured_culture_desc' => 'អត្ថបទដែលទទួលបានការចាប់អារម្មណ៍ច្រើនបំផុត',
        'featured_temples' => 'វត្តខ្មែរល្បីល្បាញ',
        'featured_temples_desc' => 'វត្តដែលមានលក្ខណៈវប្បធម៌ពិសេស',
        'upcoming_festivals' => 'ពិធីបុណ្យនាពេលខាងមុខ',
        'upcoming_festivals_desc' => 'កុំខកខានព្រឹត្តិការណ៍វប្បធម៌ពិសេស',
        'view_detail' => 'មើលលម្អិត',
        
        // CTA section
        'cta_title' => 'ចាប់ផ្តើមដំណើរស្វែងយល់',
        'cta_desc' => 'ចុះឈ្មោះគណនីឥតគិតថ្លៃដើម្បីរក្សាទុកមេរៀន តាមដានវឌ្ឍនភាព និងទទួលបានមេដាយសមិទ្ធផល។',
        'register_now' => 'ចុះឈ្មោះឥឡូវ',
        'continue_learning' => 'បន្តរៀន',
        
        // Culture category
        'culture' => 'វប្បធម៌',
        
        // Search page
        'search_placeholder' => 'បញ្ចូលពាក្យគន្លឹះស្វែងរក...',
        'search_results_for' => 'លទ្ធផលស្វែងរកសម្រាប់',
        'found_results' => 'រកឃើញ',
        'results' => 'លទ្ធផល',
        'try_different_keyword' => 'សាកល្បងស្វែងរកជាមួយពាក្យគន្លឹះផ្សេង',
        'search_content' => 'ស្វែងរកមាតិកា',
        'search_hint' => 'បញ្ចូលពាក្យគន្លឹះដើម្បីស្វែងរកវប្បធម៌ វត្ត ពិធីបុណ្យ រឿង និងមេរៀន',
        'temple' => 'វត្ត',
        'story' => 'រឿង',
        'lesson' => 'មេរៀន',
        
        // Detail pages
        'views' => 'ការមើល',
        'bookmarked' => 'បានរក្សាទុក',
        'related_articles' => 'អត្ថបទពាក់ព័ន្ធ',
        'no_comments' => 'មិនមានមតិយោបល់នៅឡើយទេ។',
        'write_comment' => 'សរសេរមតិយោបល់...',
        'send_comment' => 'ផ្ញើមតិយោបល់',
        'login_to_comment' => 'ដើម្បីផ្តល់មតិយោបល់។',
        'comment_pending' => 'មតិយោបល់កំពុងរង់ចាំការអនុម័ត!',
        'article_not_found' => 'រកមិនឃើញអត្ថបទ។',
        'link_copied' => 'បានចម្លងតំណភ្ជាប់!',
        
        // Footer
        'footer_desc' => 'វេទិកាឌីជីថលសម្រាប់អភិរក្សបេតិកភណ្ឌវប្បធម៌ខ្មែរភាគខាងត្បូង។ ស្វែងយល់ រៀនសូត្រ និងភ្ជាប់ទំនាក់ទំនងជាមួយវប្បធម៌ប្រពៃណី។',
        'footer_explore' => 'ស្វែងរក',
        'footer_learning' => 'ការសិក្សា',
        'footer_support' => 'ជំនួយ',
        'footer_leaderboard' => 'តារាងចំណាត់ថ្នាក់',
        'footer_about' => 'អំពីយើង',
        'footer_contact' => 'ទំនាក់ទំនង',
        'footer_terms' => 'លក្ខខណ្ឌប្រើប្រាស់',
        'footer_privacy' => 'គោលការណ៍ឯកជនភាព',
        'footer_copyright' => 'រក្សាសិទ្ធិ',
        
        // Page titles & descriptions
        'culture_page_title' => 'វប្បធម៌ខ្មែរ',
        'culture_page_desc' => 'ស្វែងយល់សម្រស់វប្បធម៌ប្រពៃណីរបស់ជនជាតិខ្មែរភាគខាងត្បូង',
        'culture_page_subtitle' => 'ស្វែងយល់សម្រស់វប្បធម៌ប្រពៃណីរបស់ជនជាតិខ្មែរភាគខាងត្បូងតាមរយៈអត្ថបទស៊ីជម្រៅ',
        'temple_page_title' => 'វត្តខ្មែរ',
        'temple_page_desc' => 'ស្វែងយល់ស្ថាបត្យកម្មពិសេសនៃវត្តខ្មែរភាគខាងត្បូង',
        'festival_page_title' => 'ពិធីបុណ្យខ្មែរ',
        'festival_page_desc' => 'ពិធីបុណ្យប្រពៃណីពិសេសរបស់ជនជាតិខ្មែរភាគខាងត្បូង',
        
        // Filters & sorting
        'all_categories' => 'ប្រភេទទាំងអស់',
        'all_provinces' => 'ខេត្តទាំងអស់',
        'newest' => 'ថ្មីបំផុត',
        'popular' => 'ពេញនិយម',
        'oldest' => 'ចាស់បំផុត',
        'filter' => 'ត្រង',
        'search_temple' => 'ស្វែងរកវត្ត...',
        'try_different' => 'សាកល្បងផ្លាស់ប្តូរពាក្យគន្លឹះ ឬតម្រង',
        'sort_newest' => 'ថ្មីបំផុត',
        'sort_popular' => 'ពេញនិយមបំផុត',
        'sort_oldest' => 'ចាស់បំផុត',
        'provinces' => 'ខេត្ត',
        'all_temples' => 'វត្តទាំងអស់',
        'grid_view' => 'ទម្រង់ក្រឡា',
        'map_view' => 'ផែនទី',
        'all_festivals' => 'ពិធីបុណ្យទាំងអស់',
        'all_stories' => 'រឿងទាំងអស់',
        'stories' => 'រឿង',
        'genres' => 'ប្រភេទ',
        'levels' => 'កម្រិត',
        'address' => 'អាសយដ្ឋាន',
        'province' => 'ខេត្ត',
        'coordinates' => 'កូអរដោនេ',
        'not_available' => 'មិនទាន់ធ្វើបច្ចុប្បន្នភាព',
        'manage_account' => 'គ្រប់គ្រងព័ត៌មានគណនីរបស់អ្នក',
        'enter_phone' => 'បញ្ចូលលេខទូរស័ព្ទ',
        'birthday' => 'ថ្ងៃកំណើត',
        'gender' => 'ភេទ',
        'username' => 'ឈ្មោះអ្នកប្រើប្រាស់',
        'username_hint' => 'មានតែអក្សរ លេខ និង underscore',
        'auth_feature_1' => 'ស្វែងយល់វប្បធម៌ ទំនៀមទម្លាប់ខ្មែរ',
        'auth_feature_2' => 'រៀនភាសាខ្មែរជាមួយមេរៀនអន្តរកម្ម',
        'auth_feature_3' => 'ផែនទីបេតិកភណ្ឌវប្បធម៌អន្តរកម្ម',
        'select_gender' => 'ជ្រើសរើសភេទ',
        'male' => 'ប្រុស',
        'female' => 'ស្រី',
        'other' => 'ផ្សេងទៀត',
        
        // Festival filters
        'all' => 'ទាំងអស់',
        'upcoming' => 'នាពេលខាងមុខ',
        'past' => 'កន្លងមក',
        'detail' => 'លម្អិត',
        
        // Learning page
        'learn_page_desc' => 'រៀនភាសាខ្មែរតាមរយៈមេរៀនអន្តរកម្មគួរឱ្យចាប់អារម្មណ៍',
        'learning_progress' => 'វឌ្ឍនភាពការសិក្សា',
        'basic' => 'មូលដ្ឋាន',
        'intermediate' => 'មធ្យម',
        'advanced' => 'កម្រិតខ្ពស់',
        'lesson_count' => 'មេរៀន',
        'minutes' => 'នាទី',
        'completed' => 'បានបញ្ចប់',
        'in_progress' => 'កំពុងរៀន',
        'not_started' => 'មិនទាន់រៀន',
        'review' => 'ពិនិត្យឡើងវិញ',
        'start' => 'ចាប់ផ្តើម',
        'no_lessons' => 'មិនមានមេរៀនទេ',
        'lessons_coming' => 'មេរៀននឹងត្រូវបានធ្វើបច្ចុប្បន្នភាពឆាប់ៗ។',
        'leaderboard' => 'តារាងចំណាត់ថ្នាក់',
        
        // Stories page
        'stories_page_desc' => 'ឃ្លាំងរឿងព្រេង ទេវកថា និងរឿងព្រេងនិទានខ្មែរ',
        'search_story' => 'ស្វែងរករឿង...',
        'all_genres' => 'ប្រភេទទាំងអស់',
        
        // Map page
        'map_detail' => 'លម្អិត',
        
        // Settings page
        'personal_info' => 'ព័ត៌មានផ្ទាល់ខ្លួន',
        'change_avatar' => 'ប្តូររូបភាពប្រវត្តិរូប',
        'avatar_hint' => 'JPG, PNG អតិបរមា 5MB',
        'phone' => 'លេខទូរស័ព្ទ',
        'change_password' => 'ប្តូរពាក្យសម្ងាត់',
        'current_password' => 'ពាក្យសម្ងាត់បច្ចុប្បន្ន',
        'new_password' => 'ពាក្យសម្ងាត់ថ្មី',
        'confirm_new_password' => 'បញ្ជាក់ពាក្យសម្ងាត់ថ្មី',
        'language' => 'ភាសា',
        'danger_zone' => 'តំបន់គ្រោះថ្នាក់',
        'danger_zone_desc' => 'ការលុបគណនីនឹងលុបទិន្នន័យរបស់អ្នកទាំងអស់ជាអចិន្ត្រៃយ៍។',
        'delete_account' => 'លុបគណនី',
        'update_success' => 'ធ្វើបច្ចុប្បន្នភាពព័ត៌មានជោគជ័យ!',
        'password_change_success' => 'ប្តូរពាក្យសម្ងាត់ជោគជ័យ!',
        'avatar_update_success' => 'ធ្វើបច្ចុប្បន្នភាពរូបភាពប្រវត្តិរូបជោគជ័យ!',
        'wrong_current_password' => 'ពាក្យសម្ងាត់បច្ចុប្បន្នមិនត្រឹមត្រូវ។',
        'confirm_delete' => 'តើអ្នកប្រាកដថាចង់លុបគណនីមែនទេ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានទេ។',
        'feature_developing' => 'មុខងារកំពុងត្រូវបានអភិវឌ្ឍ។',
        
        // Leaderboard page
        'leaderboard_desc' => 'អ្នកសិក្សាល្អបំផុត',
        'your_rank' => 'ចំណាត់ថ្នាក់របស់អ្នក',
        'rank' => 'ចំណាត់ថ្នាក់',
        'user' => 'អ្នកប្រើប្រាស់',
        'you' => 'អ្នក',
        'how_to_earn' => 'របៀបរកពិន្ទុ',
        'complete_lesson' => 'បញ្ចប់មេរៀន',
        'read_story' => 'អានរឿង',
        'comment_action' => 'មតិយោបល់',
        'learning_streak' => 'Streak ការសិក្សា',
        'bonus_points' => 'ពិន្ទុបន្ថែម',
        
        // Profile page
        'joined_on' => 'ចូលរួមពី',
        'points' => 'ពិន្ទុ',
        'lessons' => 'មេរៀន',
        'badges' => 'មេដាយ',
        'saved' => 'បានរក្សាទុក',
        'badges_earned' => 'មេដាយដែលទទួលបាន',
        'saved_content' => 'មាតិកាដែលបានរក្សាទុក',
        'recent_activity' => 'សកម្មភាពថ្មីៗ',
        'no_activity' => 'មិនមានសកម្មភាពនៅឡើយទេ។',
        'please_login_profile' => 'សូមចូលដើម្បីមើលប្រវត្តិរូប។',
        'invalid_session' => 'វគ្គមិនត្រឹមត្រូវ។ សូមចូលម្តងទៀត។',
        'manage_your_account' => 'គ្រប់គ្រងព័ត៌មានផ្ទាល់ខ្លួនរបស់អ្នក',
        'register_subtitle' => 'ចូលរួមសហគមន៍សិក្សា និងស្វែងយល់វប្បធម៌',
        
        // Auth pages
        'welcome_back' => 'សូមស្វាគមន៍មកវិញ!',
        'create_account' => 'បង្កើតគណនីដើម្បីចាប់ផ្តើមរៀន',
        'or' => 'ឬ',
        'session_expired' => 'វគ្គផុតកំណត់។ សូមព្យាយាមម្តងទៀត។',
        'fill_all_info' => 'សូមបំពេញព័ត៌មានទាំងអស់។',
        'invalid_email' => 'អ៊ីមែលមិនត្រឹមត្រូវ។',
        'password_min' => 'ពាក្យសម្ងាត់ត្រូវមានយ៉ាងហោចណាស់ ៦ តួអក្សរ។',
        'password_not_match' => 'ពាក្យសម្ងាត់បញ្ជាក់មិនត្រូវគ្នា។',
        'agree_terms' => 'អ្នកត្រូវយល់ព្រមលក្ខខណ្ឌប្រើប្រាស់។',
        'email_exists' => 'អ៊ីមែលនេះត្រូវបានប្រើរួចហើយ។',
        'wrong_credentials' => 'អ៊ីមែល ឬពាក្យសម្ងាត់មិនត្រឹមត្រូវ។',
        'register_success' => 'ចុះឈ្មោះជោគជ័យ! សូមចូល។',
        'login_success' => 'ចូលជោគជ័យ!',
        'error_occurred' => 'មានបញ្ហាកើតឡើង។ សូមព្យាយាមម្តងទៀត។',
        'terms_agree' => 'ខ្ញុំយល់ព្រមជាមួយ',
        'terms_of_use' => 'លក្ខខណ្ឌប្រើប្រាស់',
        'and' => 'និង',
        'min_6_chars' => 'យ៉ាងហោចណាស់ ៦ តួអក្សរ',
        'retype_password' => 'វាយពាក្យសម្ងាត់ម្តងទៀត',
        
        // Forum
        'forum_title' => 'វេទិកាសហគមន៍',
        'forum_subtitle' => 'កន្លែងភ្ជាប់ ចែករំលែកចំណេះដឹង និងពិភាក្សាអំពីវប្បធម៌ខ្មែរភាគខាងត្បូង',
        'topics' => 'ប្រធានបទ',
        'posts' => 'ប្រកាស',
        'members' => 'សមាជិក',
        'discussion_categories' => 'ប្រភេទពិភាក្សា',
        'no_categories' => 'មិនមានប្រភេទនៅឡើយទេ',
        'forum_setup_msg' => 'ប្រព័ន្ធវេទិកាកំពុងត្រូវបានរៀបចំ។ សូមត្រឡប់មកវិញនៅពេលក្រោយ!',
        'create_new_topic' => 'បង្កើតប្រធានបទថ្មី',
        'login_to_join' => 'ចូលដើម្បីចូលរួម',
        'latest_discussions' => 'ការពិភាក្សាថ្មីបំផុត',
        'no_topics_yet' => 'មិនមានប្រធានបទនៅឡើយទេ',
        'active_members' => 'សមាជិកសកម្ម',
        
        // Forum Category Page
        'forum' => 'វេទិកា',
        'topic_list' => 'បញ្ជីប្រធានបទ',
        'topic_count' => 'ប្រធានបទ',
        'page' => 'ទំព័រ',
        'pinned' => 'ខ្ទាស់',
        'locked' => 'ចាក់សោ',
        'hot' => 'ក្តៅ',
        'new' => 'ថ្មី',
        'replies' => 'ឆ្លើយតប',
        'views_count' => 'ការមើល',
        'no_topics_in_category' => 'មិនមានប្រធានបទនៅឡើយទេ',
        'be_first_to_create' => 'ក្លាយជាអ្នកដំបូងបង្កើតប្រធានបទក្នុងប្រភេទនេះ!',
        'prev' => 'មុន',
        'next' => 'បន្ទាប់',
        'quick_stats' => 'ស្ថិតិរហ័ស',
        'other_categories' => 'ប្រភេទផ្សេងទៀត',
        'back_to_forum' => 'ត្រឡប់ទៅវេទិកា',
        'view_all_categories' => 'មើលប្រភេទទាំងអស់',
        'no_replies' => 'មិនមានការឆ្លើយតប',
    ]
];

// Get translation function
function __($key) {
    global $translations, $lang;
    return $translations[$lang][$key] ?? $key;
}

// Get current language
function getCurrentLang() {
    global $lang;
    return $lang;
}
?>
