<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $templates = [

            // ═══════════════════════════════════════════════════════════════
            //  ANALYTICS
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'analytics_followers',
                'title_en' => 'New Follower on {shop_name}',
                'title_ar' => 'متابع جديد لـ {shop_name}',
                'content_en' => '🎉 {follower_name} just followed your shop "{shop_name}"! Your community keeps growing — keep sharing your shop to attract more followers.',
                'content_ar' => '🎉 تابعك {follower_name} في متجر "{shop_name}"! مجتمعك ينمو باستمرار — واصل مشاركة متجرك لجذب المزيد من المتابعين.',
            ],
            [
                'key' => 'analytics_likes',
                'title_en' => 'Engagement Milestone for {shop_name}',
                'title_ar' => 'إنجاز تفاعل لـ {shop_name}',
                'content_en' => '🔥 Great news! Your shop "{shop_name}" reached a new milestone in total likes. Your audience loves what you\'re doing — keep up the momentum!',
                'content_ar' => '🔥 أخبار رائعة! وصل متجرك "{shop_name}" إلى إنجاز جديد في إجمالي الإعجابات. جمهورك يحب ما تقدمه — حافظ على هذا الزخم!',
            ],
            [
                'key' => 'analytics_ratings',
                'title_en' => 'Rating Update for {shop_name}',
                'title_ar' => 'تحديث تقييم {shop_name}',
                'content_en' => '⭐ The rating for "{shop_name}" has been updated. Customers have shared their feedback — see what they love and where you can improve.',
                'content_ar' => '⭐ تم تحديث تقييم "{shop_name}". شارك العملاء بملاحظاتهم — تعرف على ما يحبونه وأين يمكنك التحسين.',
            ],
            [
                'key' => 'analytics_views',
                'title_en' => 'Views Milestone for {shop_name}',
                'title_ar' => 'إنجاز مشاهدات لـ {shop_name}',
                'content_en' => '👀 Your shop "{shop_name}" just reached {count} views! Your visibility is growing fast — keep optimizing your shop to attract even more visitors.',
                'content_ar' => '👀 وصل متجرك "{shop_name}" إلى {count} مشاهدة! رؤيتك تنمو بسرعة — واصل تحسين متجرك لجذب المزيد من الزوار.',
            ],
            [
                'key' => 'analytics_overview',
                'title_en' => 'Weekly Analytics for {shop_name}',
                'title_ar' => 'تحليلات {shop_name} الأسبوعية',
                'content_en' => '📊 Your weekly analytics for "{shop_name}" are ready! Check your views, likes, followers, and rating trends to see how your shop performed this week.',
                'content_ar' => '📊 تحليلاتك الأسبوعية لـ "{shop_name}" جاهزة! تفقد اتجاهات المشاهدات والإعجابات والمتابعين والتقييم لترى أداء متجرك هذا الأسبوع.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  COMMENTS
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'new_comment',
                'title_en' => 'New Comment from {user_name}',
                'title_ar' => 'تعليق جديد من {user_name}',
                'content_en' => '💬 {user_name} commented on your shop "{shop_name}": "{comment_snippet}". Tap to reply and engage with your customer.',
                'content_ar' => '💬 علق {user_name} على متجرك "{shop_name}": "{comment_snippet}". اضغط للرد والتفاعل مع عميلك.',
            ],
            [
                'key' => 'comment_reply',
                'title_en' => '{user_name} Replied to a Comment',
                'title_ar' => 'رد {user_name} على تعليق',
                'content_en' => '↩️ {user_name} replied to a comment on your shop "{shop_name}": "{comment_snippet}". Join the conversation and keep your community engaged.',
                'content_ar' => '↩️ رد {user_name} على تعليق في متجرك "{shop_name}": "{comment_snippet}". شارك في المحادثة وحافظ على تفاعل مجتمعك.',
            ],
            [
                'key' => 'comment_reported',
                'title_en' => 'Comment Reported on {shop_name}',
                'title_ar' => 'تم الإبلاغ عن تعليق في {shop_name}',
                'content_en' => '🚩 A comment on your shop "{shop_name}" has been reported (reason: {reason}). Comment: "{comment_snippet}". Please review and take appropriate action.',
                'content_ar' => '🚩 تم الإبلاغ عن تعليق على متجرك "{shop_name}" (السبب: {reason}). التعليق: "{comment_snippet}". يرجى المراجعة واتخاذ الإجراء المناسب.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  PRODUCTS
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'product_reported',
                'title_en' => 'Product "{product_name}" Reported',
                'title_ar' => 'تم الإبلاغ عن المنتج "{product_name}"',
                'content_en' => '🚩 Your product "{product_name}" in shop "{shop_name}" has been reported (reason: {reason}). Please review the report details and take appropriate action.',
                'content_ar' => '🚩 تم الإبلاغ عن منتجك "{product_name}" في متجر "{shop_name}" (السبب: {reason}). يرجى مراجعة تفاصيل البلاغ واتخاذ الإجراء المناسب.',
            ],
            [
                'key' => 'product_engagement',
                'title_en' => '{product_name} is Getting Attention!',
                'title_ar' => '{product_name} يحظى بالاهتمام!',
                'content_en' => '📈 Your product "{product_name}" in "{shop_name}" is trending! Check out the recent likes, comments, and shares. Great job keeping your audience engaged!',
                'content_ar' => '📈 منتجك "{product_name}" في "{shop_name}" في ازدياد! تفقد الإعجابات والتعليقات والمشاركات الأخيرة. عمل رائع في إبقاء جمهورك متفاعلاً!',
            ],
            [
                'key' => 'product_performance',
                'title_en' => '{product_name}: Performance Update',
                'title_ar' => '{product_name}: تحديث الأداء',
                'content_en' => '📊 Your product "{product_name}" in "{shop_name}" has updated performance metrics. Check views, ratings, and engagement to see how it\'s doing this week.',
                'content_ar' => '📊 منتجك "{product_name}" في "{shop_name}" لديه مقاييس أداء محدثة. تفقد المشاهدات والتقييمات والتفاعل لترى أداءه هذا الأسبوع.',
            ],
            [
                'key' => 'product_comments',
                'title_en' => 'New Comment on {product_name}',
                'title_ar' => 'تعليق جديد على {product_name}',
                'content_en' => '💬 Someone commented on your product "{product_name}" in "{shop_name}". See what customers are saying and reply to keep the conversation going.',
                'content_ar' => '💬 علق شخص ما على منتجك "{product_name}" في "{shop_name}". تعرف على آراء العملاء ورد للحفاظ على استمرار المحادثة.',
            ],
            [
                'key' => 'product_liked',
                'title_en' => '{user_name} Liked {product_name}',
                'title_ar' => 'أعجب {user_name} بـ {product_name}',
                'content_en' => '❤️ {user_name} liked your product "{product_name}" in "{shop_name}". Your product is gaining popularity — keep up the great work!',
                'content_ar' => '❤️ أعجب {user_name} بمنتجك "{product_name}" في "{shop_name}". منتجك يكتسب شعبية — واصل عملك الرائع!',
            ],
            [
                'key' => 'product_favorited',
                'title_en' => '{user_name} Favorited {product_name}',
                'title_ar' => 'أضاف {user_name} {product_name} للمفضلة',
                'content_en' => '⭐ {user_name} added your product "{product_name}" from "{shop_name}" to their favorites. Someone\'s interested — consider running a promotion!',
                'content_ar' => '⭐ أضاف {user_name} منتجك "{product_name}" من "{shop_name}" إلى المفضلة. هناك من يهتم به — فكر في تقديم عرض ترويجي!',
            ],
            [
                'key' => 'product_out_of_stock',
                'title_en' => '⚠️ {product_name} is Out of Stock',
                'title_ar' => '⚠️ {product_name} نفد من المخزون',
                'content_en' => '⚠️ Your product "{product_name}" in "{shop_name}" is out of stock! Restock soon to avoid losing sales and keep your customers satisfied.',
                'content_ar' => '⚠️ منتجك "{product_name}" في "{shop_name}" نفد من المخزون! قم بتزويده قريباً لتجنب فقدان المبيعات والحفاظ على رضا عملائك.',
            ],
            [
                'key' => 'product_discount',
                'title_en' => 'Discount Updated for {product_name}',
                'title_ar' => 'تحديث الخصم على {product_name}',
                'content_en' => '🏷️ The discount for your product "{product_name}" has been changed. Customers will see the new price — this is a great time to attract more buyers!',
                'content_ar' => '🏷️ تم تغيير الخصم على منتجك "{product_name}". سيرى العملاء السعر الجديد — هذا وقت رائع لجذب المزيد من المشترين!',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  SHOP
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'shop_reported',
                'title_en' => '{shop_name} Has Been Reported',
                'title_ar' => 'تم الإبلاغ عن {shop_name}',
                'content_en' => '🚩 Your shop "{shop_name}" has been reported (reason: {reason}). Our team will review the report. If you have any questions, please contact support.',
                'content_ar' => '🚩 تم الإبلاغ عن متجرك "{shop_name}" (السبب: {reason}). سيقوم فريقنا بمراجعة البلاغ. إذا كان لديك أي استفسار، يرجى التواصل مع الدعم.',
            ],
            [
                'key' => 'report_resolved',
                'title_en' => 'Report Resolved ✓',
                'title_ar' => 'تم حل البلاغ ✓',
                'content_en' => '✅ The report filed against your content has been reviewed and resolved. No further action is needed from your side.',
                'content_ar' => '✅ تمت مراجعة وحل البلاغ المقدم ضد محتواك. لا حاجة لأي إجراء إضافي من جانبك.',
            ],
            [
                'key' => 'verification_status',
                'title_en' => 'Verification Update for {shop_name}',
                'title_ar' => 'تحديث توثيق {shop_name}',
                'content_en' => '📋 Your verification status for "{shop_name}" has been updated. Check the verification section for the latest details and next steps.',
                'content_ar' => '📋 تم تحديث حالة توثيق "{shop_name}". تفقد قسم التوثيق للحصول على أحدث التفاصيل والخطوات التالية.',
            ],
            [
                'key' => 'shop_verified',
                'title_en' => '✅ {shop_name} is Now Verified!',
                'title_ar' => '✅ تم توثيق {shop_name}!',
                'content_en' => '✅ Congratulations! Your shop "{shop_name}" has been officially verified. Your verification badge will increase customer trust and visibility. Enjoy the benefits!',
                'content_ar' => '✅ تهانينا! تم توثيق متجرك "{shop_name}" رسمياً. شارة التوثيق ستزيد من ثقة العملاء وظهور متجرك. استمتع بالمزايا!',
            ],
            [
                'key' => 'shop_rejected',
                'title_en' => 'Verification Not Approved — {shop_name}',
                'title_ar' => 'لم يتم اعتماد توثيق {shop_name}',
                'content_en' => '❌ Your verification request for "{shop_name}" was not approved (reason: {reason}). Please review the requirements, update your information, and submit a new request.',
                'content_ar' => '❌ لم تتم الموافقة على طلب توثيق "{shop_name}" (السبب: {reason}). يرجى مراجعة المتطلبات وتحديث معلوماتك وتقديم طلب جديد.',
            ],
            [
                'key' => 'shop_suspended',
                'title_en' => '⚠️ {shop_name} Has Been Suspended',
                'title_ar' => '⚠️ تم تعليق {shop_name}',
                'content_en' => '⚠️ Your shop "{shop_name}" has been suspended (reason: {reason}). Please contact support for assistance and more information about reinstatement.',
                'content_ar' => '⚠️ تم تعليق متجرك "{shop_name}" (السبب: {reason}). يرجى التواصل مع الدعم للمساعدة ومزيد من المعلومات حول إعادة التنشيط.',
            ],
            [
                'key' => 'shop_status',
                'title_en' => 'Status Update for {shop_name}',
                'title_ar' => 'تحديث حالة {shop_name}',
                'content_en' => '📋 The status of your shop "{shop_name}" has been updated. Check your dashboard for the latest changes and manage your shop accordingly.',
                'content_ar' => '📋 تم تحديث حالة متجرك "{shop_name}". تفقد لوحة التحكم لأحدث التغييرات وإدارة متجرك وفقاً لذلك.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  ONBOARDING
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'welcome',
                'title_en' => 'Welcome {user_name}!',
                'title_ar' => 'مرحباً {user_name}!',
                'content_en' => '👋 Welcome to ShopsMap, {user_name}! Start exploring shops and products near you, follow your favorites, and set up your profile to get the best experience.',
                'content_ar' => '👋 مرحباً بك {user_name} في شوبس ماب! ابدأ باستكشاف المتاجر والمنتجات القريبة منك، وتابع المفضلة لديك، وقم بإعداد ملفك الشخصي للحصول على أفضل تجربة.',
            ],
            [
                'key' => 'shop_registered',
                'title_en' => '🎉 {shop_name} is Now Live!',
                'title_ar' => '🎉 {shop_name} أصبح متاحاً الآن!',
                'content_en' => '🎉 Congratulations! Your shop "{shop_name}" has been registered successfully. Start adding products, upload photos, and customize your shop page to attract customers.',
                'content_ar' => '🎉 تهانينا! تم تسجيل متجرك "{shop_name}" بنجاح. ابدأ بإضافة المنتجات وتحميل الصور وتخصيص صفحة متجرك لجذب العملاء.',
            ],
            [
                'key' => 'password_changed',
                'title_en' => 'Password Changed Successfully',
                'title_ar' => 'تم تغيير كلمة المرور بنجاح',
                'content_en' => '🔐 Your account password was changed just now. If you made this change, you\'re all set. If not, please contact support immediately to secure your account.',
                'content_ar' => '🔐 تم تغيير كلمة مرور حسابك للتو. إذا قمت بهذا التغيير، فكل شيء على ما يرام. إذا لم تكن أنت، يرجى الاتصال بالدعم فوراً لتأمين حسابك.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  DASHBOARD
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'dashboard',
                'title_en' => '{shop_name} Dashboard Updated',
                'title_ar' => 'تم تحديث لوحة تحكم {shop_name}',
                'content_en' => '📊 Your shop dashboard has new updates! Check your latest sales, views, follower growth, and engagement metrics to track your shop\'s performance.',
                'content_ar' => '📊 لوحة تحكم متجرك بها تحديثات جديدة! تفقد أحدث المبيعات والمشاهدات ونمو المتابعين ومقاييس التفاعل لتتبع أداء متجرك.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  SUPPORT
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'support',
                'title_en' => 'Support Update — Ticket #{id}',
                'title_ar' => 'تحديث الدعم — تذكرة #{id}',
                'content_en' => '📬 There\'s an update on your support request "{ticket_subject}" (Ticket #{id}). Tap to see the latest response from our support team.',
                'content_ar' => '📬 هناك تحديث بخصوص طلب الدعم "{ticket_subject}" (تذكرة #{id}). اضغط لرؤية آخر رد من فريق الدعم.',
            ],
            [
                'key' => 'ticket_created',
                'title_en' => 'Ticket Created — {ticket_subject}',
                'title_ar' => 'تم إنشاء التذكرة — {ticket_subject}',
                'content_en' => '🎫 Your support ticket "{ticket_subject}" has been created successfully. Our team will review it and get back to you as soon as possible. You\'ll receive a notification when we respond.',
                'content_ar' => '🎫 تم إنشاء تذكرتك "{ticket_subject}" في الدعم بنجاح. سيراجع فريقنا طلبك وسنرد عليك في أقرب وقت ممكن. ستتلقى إشعاراً عندما نرد.',
            ],
            [
                'key' => 'ticket_replied',
                'title_en' => '💬 Support Replied — {ticket_subject}',
                'title_ar' => '💬 رد من الدعم — {ticket_subject}',
                'content_en' => '💬 The support team has replied to your ticket "{ticket_subject}": "{reply_preview}". Tap to read their full response and continue the conversation.',
                'content_ar' => '💬 قام فريق الدعم بالرد على تذكرتك "{ticket_subject}": "{reply_preview}". اضغط لقراءة الرد كاملاً ومتابعة المحادثة.',
            ],
            [
                'key' => 'ticket_resolved',
                'title_en' => '✅ Ticket Resolved — {ticket_subject}',
                'title_ar' => '✅ تم حل التذكرة — {ticket_subject}',
                'content_en' => '✅ Your support ticket "{ticket_subject}" has been marked as resolved by our team. If your issue persists, feel free to reply to reopen the ticket or create a new one.',
                'content_ar' => '✅ تم وضع علامة "تم الحل" على تذكرتك "{ticket_subject}" من قبل فريقنا. إذا استمرت المشكلة، يمكنك الرد لإعادة فتح التذكرة أو إنشاء تذكرة جديدة.',
            ],
            [
                'key' => 'ticket_closed',
                'title_en' => '📁 Ticket Closed — {ticket_subject}',
                'title_ar' => '📁 تم إغلاق التذكرة — {ticket_subject}',
                'content_en' => '📁 Your support ticket "{ticket_subject}" has been closed. Thank you for reaching out! If you need further assistance, you can create a new ticket anytime.',
                'content_ar' => '📁 تم إغلاق تذكرتك "{ticket_subject}". شكراً لتواصلك! إذا كنت بحاجة إلى مساعدة إضافية، يمكنك إنشاء تذكرة جديدة في أي وقت.',
            ],
            [
                'key' => 'ticket_follow_up',
                'title_en' => '📬 Follow-Up Needed — {ticket_subject}',
                'title_ar' => '📬 متابعة مطلوبة — {ticket_subject}',
                'content_en' => '📬 We need more information to resolve your ticket "{ticket_subject}". Please provide additional details so our support team can help you better.',
                'content_ar' => '📬 نحتاج إلى مزيد من المعلومات لحل تذكرتك "{ticket_subject}". يرجى تقديم تفاصيل إضافية حتى يتمكن فريق الدعم من مساعدتك بشكل أفضل.',
            ],
            [
                'key' => 'ticket_status_change',
                'title_en' => 'Status Changed — {ticket_subject}',
                'title_ar' => 'تغيرت حالة التذكرة — {ticket_subject}',
                'content_en' => '🔄 Your support ticket "{ticket_subject}" status changed to: {status}. Tap to see the full details and any updates from our team.',
                'content_ar' => '🔄 تغيرت حالة تذكرتك "{ticket_subject}" إلى: {status}. اضغط لرؤية التفاصيل الكاملة وأي تحديثات من فريقنا.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  PROFILE & SETTINGS
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'profile',
                'title_en' => 'Profile Updated',
                'title_ar' => 'تم تحديث الملف الشخصي',
                'content_en' => '👤 Your profile information has been updated successfully. All changes are now visible to other users.',
                'content_ar' => '👤 تم تحديث معلومات ملفك الشخصي بنجاح. جميع التغييرات أصبحت مرئية للمستخدمين الآخرين.',
            ],
            [
                'key' => 'settings',
                'title_en' => 'Settings Updated',
                'title_ar' => 'تم تحديث الإعدادات',
                'content_en' => '⚙️ Your app settings have been updated successfully. These changes will take effect immediately.',
                'content_ar' => '⚙️ تم تحديث إعدادات التطبيق بنجاح. هذه التغييرات ستصبح سارية المفعول فوراً.',
            ],
            [
                'key' => 'settings_security',
                'title_en' => 'Security Settings Updated',
                'title_ar' => 'تم تحديث إعدادات الأمان',
                'content_en' => '🔒 Your security settings have been updated. Regular security reviews help keep your account safe.',
                'content_ar' => '🔒 تم تحديث إعدادات الأمان الخاصة بك. المراجعة الدورية للأمان تساعد في الحفاظ على أمان حسابك.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  MAP
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'map',
                'title_en' => 'Discover Nearby Shops & Products',
                'title_ar' => 'اكتشف المتاجر والمنتجات القريبة',
                'content_en' => '📍 New shops and products are available near your location! Open the map to explore what\'s around and discover great finds.',
                'content_ar' => '📍 متاجر ومنتجات جديدة متاحة بالقرب من موقعك! افتح الخريطة لاستكشاف ما حولك واكتشاف منتجات رائعة.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  SYSTEM
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'system_announcement',
                'title_en' => '📢 Announcement',
                'title_ar' => '📢 إعلان',
                'content_en' => '📢 {message}',
                'content_ar' => '📢 {message}',
            ],
            [
                'key' => 'system_info',
                'title_en' => 'ℹ️ Information',
                'title_ar' => 'ℹ️ معلومات',
                'content_en' => 'ℹ️ {message}',
                'content_ar' => 'ℹ️ {message}',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  ORDERS
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'order_update',
                'title_en' => 'Order #{order_id} — {status}',
                'title_ar' => 'الطلب #{order_id} — {status}',
                'content_en' => '📦 Your order #{order_id} status has been updated to: {status}. Tap to track your order and see estimated delivery details.',
                'content_ar' => '📦 تم تحديث حالة طلبك #{order_id} إلى: {status}. اضغط لتتبع طلبك ومعرفة تفاصيل التوصيل المتوقعة.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  PAYMENTS
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'payment_received',
                'title_en' => '💰 Payment Received — {amount}',
                'title_ar' => '💰 تم استلام دفعة — {amount}',
                'content_en' => '💰 A payment of {amount} has been received for your shop. Check your wallet or bank account for the updated balance.',
                'content_ar' => '💰 تم استلام دفعة بقيمة {amount} لمتجرك. تفقد محفظتك أو حسابك البنكي لرؤية الرصيد المحدث.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  MILESTONES
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'follower_milestone',
                'title_en' => '🏆 {count} Followers — {shop_name}',
                'title_ar' => '🏆 {count} متابع — {shop_name}',
                'content_en' => '🏆 Congratulations! Your shop "{shop_name}" reached {count} followers! Your community is growing strong — keep providing great value to your audience.',
                'content_ar' => '🏆 تهانينا! وصل متجرك "{shop_name}" إلى {count} متابع! مجتمعك ينمو بقوة — واصل تقديم قيمة رائعة لجمهورك.',
            ],
            [
                'key' => 'like_milestone',
                'title_en' => '⭐ {count} Likes — {shop_name}',
                'title_ar' => '⭐ {count} إعجاب — {shop_name}',
                'content_en' => '⭐ Your shop "{shop_name}" reached {count} likes! Your content is resonating with people — keep creating amazing experiences for your audience.',
                'content_ar' => '⭐ وصل متجرك "{shop_name}" إلى {count} إعجاب! محتواك يلقى صدى لدى الناس — واصل إنشاء تجارب رائعة لجمهورك.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  COMPLIANCE
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'content_removed',
                'title_en' => 'Content Removed — Policy Violation',
                'title_ar' => 'تم حذف المحتوى — انتهاك السياسة',
                'content_en' => '⚠️ Some of your content has been removed for violating our community guidelines. Please review the rules carefully to prevent future actions on your account.',
                'content_ar' => '⚠️ تم حذف بعض محتواك لانتهاكه إرشادات المجتمع. يرجى مراجعة القواعد بعناية لمنع الإجراءات المستقبلية على حسابك.',
            ],
            [
                'key' => 'warning',
                'title_en' => '⚠️ Account Warning',
                'title_ar' => '⚠️ تحذير الحساب',
                'content_en' => '⚠️ You have received a warning regarding your account activity. Please review our community guidelines to ensure compliance and avoid further restrictions.',
                'content_ar' => '⚠️ لقد تلقيت تحذيراً بخصوص نشاط حسابك. يرجى مراجعة إرشادات المجتمع لضمان الامتثال وتجنب القيود الإضافية.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  SECURITY
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'login_new_device',
                'title_en' => '🔐 New Login to Your Account',
                'title_ar' => '🔐 تسجيل دخول جديد لحسابك',
                'content_en' => '🔐 Your account was logged into from a new device. If this was you, you can ignore this notification. If not, secure your account immediately by changing your password.',
                'content_ar' => '🔐 تم تسجيل الدخول إلى حسابك من جهاز جديد. إذا كان هذا أنت، يمكنك تجاهل هذا الإشعار. إذا لم يكن الأمر كذلك، قم بتأمين حسابك فوراً بتغيير كلمة المرور.',
            ],
            [
                'key' => 'account_suspended',
                'title_en' => '🚫 Account Suspended',
                'title_ar' => '🚫 تم تعليق الحساب',
                'content_en' => '🚫 Your account has been suspended. Please contact our support team for more information on how to reinstate your account and regain access.',
                'content_ar' => '🚫 تم تعليق حسابك. يرجى التواصل مع فريق الدعم لدينا لمزيد من المعلومات حول كيفية إعادة تنشيط حسابك واستعادة الوصول.',
            ],

            // ═══════════════════════════════════════════════════════════════
            //  STANDARD UPDATES
            // ═══════════════════════════════════════════════════════════════

            [
                'key' => 'profile_updated',
                'title_en' => 'Profile Updated Successfully',
                'title_ar' => 'تم تحديث الملف الشخصي بنجاح',
                'content_en' => '👤 Your profile information was updated successfully. All changes are now live and visible to other users.',
                'content_ar' => '👤 تم تحديث معلومات ملفك الشخصي بنجاح. جميع التغييرات أصبحت سارية الآن ومرئية للمستخدمين الآخرين.',
            ],
            [
                'key' => 'shop_updated',
                'title_en' => '{shop_name} Details Updated',
                'title_ar' => 'تم تحديث بيانات {shop_name}',
                'content_en' => '🏪 Your shop "{shop_name}" details were updated successfully. Customers will now see the latest information about your shop.',
                'content_ar' => '🏪 تم تحديث تفاصيل متجرك "{shop_name}" بنجاح. سيرى العملاء الآن أحدث المعلومات عن متجرك.',
            ],

        ];

        foreach ($templates as $t) {
            $existing = DB::table('notification_templates')
                ->where('key', $t['key'])
                ->first();

            $data = array_merge($t, [
                'is_active' => true,
                'version' => DB::raw('version + 1'),
                'updated_at' => $now,
            ]);

            if ($existing) {
                $data['version'] = $existing->version + 1;
                DB::table('notification_templates')
                    ->where('key', $t['key'])
                    ->update($data);
            } else {
                $data['version'] = 1;
                $data['created_at'] = $now;
                DB::table('notification_templates')->insert($data);
            }
        }
    }
}
