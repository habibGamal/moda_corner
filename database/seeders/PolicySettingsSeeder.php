<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class PolicySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'privacy_policy_en',
                'group' => 'legal',
                'type' => 'textarea',
                'value' => '<h2>Privacy Policy</h2>
<p>This Privacy Policy describes how we collect, use, and protect your information when you use our website.</p>

<h3>Information We Collect</h3>
<p>We collect information you provide directly to us, such as when you create an account, make a purchase, or contact us.</p>

<h3>How We Use Your Information</h3>
<p>We use the information we collect to provide, maintain, and improve our services.</p>

<h3>Information Sharing</h3>
<p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent.</p>

<h3>Data Security</h3>
<p>We implement appropriate security measures to protect your personal information.</p>

<h3>Contact Us</h3>
<p>If you have any questions about this Privacy Policy, please contact us.</p>',
                'label_en' => 'Privacy Policy (English)',
                'label_ar' => 'سياسة الخصوصية (إنجليزي)',
                'description_en' => 'Privacy policy content in English',
                'description_ar' => 'محتوى سياسة الخصوصية باللغة الإنجليزية',
                'is_required' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'privacy_policy_ar',
                'group' => 'legal',
                'type' => 'textarea',
                'value' => '<h2>سياسة الخصوصية</h2>
<p>تصف سياسة الخصوصية هذه كيفية جمع واستخدام وحماية معلوماتك عند استخدام موقعنا الإلكتروني.</p>

<h3>المعلومات التي نجمعها</h3>
<p>نجمع المعلومات التي تقدمها لنا مباشرة، مثل عند إنشاء حساب أو إجراء عملية شراء أو الاتصال بنا.</p>

<h3>كيفية استخدام معلوماتك</h3>
<p>نستخدم المعلومات التي نجمعها لتوفير وصيانة وتحسين خدماتنا.</p>

<h3>مشاركة المعلومات</h3>
<p>لا نبيع أو نتاجر أو ننقل معلوماتك الشخصية إلى أطراف ثالثة بدون موافقتك.</p>

<h3>أمان البيانات</h3>
<p>نطبق تدابير أمنية مناسبة لحماية معلوماتك الشخصية.</p>

<h3>اتصل بنا</h3>
<p>إذا كان لديك أي أسئلة حول سياسة الخصوصية هذه، يرجى الاتصال بنا.</p>',
                'label_en' => 'Privacy Policy (Arabic)',
                'label_ar' => 'سياسة الخصوصية (عربي)',
                'description_en' => 'Privacy policy content in Arabic',
                'description_ar' => 'محتوى سياسة الخصوصية باللغة العربية',
                'is_required' => true,
                'display_order' => 2,
            ],
            [
                'key' => 'return_policy_en',
                'group' => 'legal',
                'type' => 'textarea',
                'value' => '<h2>Return Policy</h2>
<p>We want you to be completely satisfied with your purchase. If you are not satisfied, you may return items according to the terms below.</p>

<h3>Return Window</h3>
<p>You have 30 days from the date of delivery to return an item for a full refund.</p>

<h3>Condition of Items</h3>
<p>Items must be returned in their original condition, unused, and in original packaging.</p>

<h3>Return Process</h3>
<p>To initiate a return, please contact our customer service team with your order number and reason for return.</p>

<h3>Refunds</h3>
<p>Refunds will be processed to your original payment method within 5-7 business days after we receive your return.</p>

<h3>Exchanges</h3>
<p>We currently do not offer direct exchanges. Please return the item for a refund and place a new order.</p>

<h3>Non-Returnable Items</h3>
<p>Certain items such as personalized products, perishables, and intimate items cannot be returned.</p>',
                'label_en' => 'Return Policy (English)',
                'label_ar' => 'سياسة الإرجاع (إنجليزي)',
                'description_en' => 'Return policy content in English',
                'description_ar' => 'محتوى سياسة الإرجاع باللغة الإنجليزية',
                'is_required' => true,
                'display_order' => 3,
            ],
            [
                'key' => 'return_policy_ar',
                'group' => 'legal',
                'type' => 'textarea',
                'value' => '<h2>سياسة الإرجاع</h2>
<p>نريد أن تكون راضيًا تمامًا عن مشترياتك. إذا لم تكن راضيًا، يمكنك إرجاع العناصر وفقًا للشروط أدناه.</p>

<h3>فترة الإرجاع</h3>
<p>لديك 30 يومًا من تاريخ التسليم لإرجاع عنصر واسترداد كامل المبلغ.</p>

<h3>حالة العناصر</h3>
<p>يجب إرجاع العناصر في حالتها الأصلية، غير مستخدمة، وفي التغليف الأصلي.</p>

<h3>عملية الإرجاع</h3>
<p>لبدء عملية الإرجاع، يرجى الاتصال بفريق خدمة العملاء مع رقم طلبك وسبب الإرجاع.</p>

<h3>المبالغ المستردة</h3>
<p>ستتم معالجة المبالغ المستردة إلى طريقة الدفع الأصلية خلال 5-7 أيام عمل بعد استلام إرجاعك.</p>

<h3>التبديل</h3>
<p>لا نقدم حاليًا تبديلات مباشرة. يرجى إرجاع العنصر لاسترداد المبلغ وتقديم طلب جديد.</p>

<h3>العناصر غير القابلة للإرجاع</h3>
<p>بعض العناصر مثل المنتجات المخصصة والقابلة للتلف والعناصر الشخصية لا يمكن إرجاعها.</p>',
                'label_en' => 'Return Policy (Arabic)',
                'label_ar' => 'سياسة الإرجاع (عربي)',
                'description_en' => 'Return policy content in Arabic',
                'description_ar' => 'محتوى سياسة الإرجاع باللغة العربية',
                'is_required' => true,
                'display_order' => 4,
            ],
            [
                'key' => 'terms_of_service_en',
                'group' => 'legal',
                'type' => 'textarea',
                'value' => '<h2>Terms of Service</h2>
<p>By using our website, you agree to comply with and be bound by the following terms and conditions.</p>

<h3>Use of Website</h3>
<p>You may use our website for lawful purposes only. You agree not to use the site in any way that could damage or impair the site.</p>

<h3>Product Information</h3>
<p>We strive to provide accurate product information, but we do not warrant that product descriptions are error-free.</p>

<h3>Pricing</h3>
<p>All prices are subject to change without notice. We reserve the right to modify or cancel orders due to pricing errors.</p>

<h3>User Accounts</h3>
<p>You are responsible for maintaining the confidentiality of your account information and password.</p>

<h3>Limitation of Liability</h3>
<p>We shall not be liable for any direct, indirect, incidental, or consequential damages arising from your use of the website.</p>',
                'label_en' => 'Terms of Service (English)',
                'label_ar' => 'شروط الخدمة (إنجليزي)',
                'description_en' => 'Terms of service content in English',
                'description_ar' => 'محتوى شروط الخدمة باللغة الإنجليزية',
                'is_required' => true,
                'display_order' => 5,
            ],
            [
                'key' => 'terms_of_service_ar',
                'group' => 'legal',
                'type' => 'textarea',
                'value' => '<h2>شروط الخدمة</h2>
<p>باستخدام موقعنا الإلكتروني، فإنك توافق على الالتزام بالشروط والأحكام التالية.</p>

<h3>استخدام الموقع</h3>
<p>يمكنك استخدام موقعنا للأغراض القانونية فقط. توافق على عدم استخدام الموقع بأي طريقة قد تضر أو تعيق الموقع.</p>

<h3>معلومات المنتج</h3>
<p>نسعى لتوفير معلومات دقيقة عن المنتجات، لكننا لا نضمن أن أوصاف المنتجات خالية من الأخطاء.</p>

<h3>التسعير</h3>
<p>جميع الأسعار قابلة للتغيير دون إشعار مسبق. نحتفظ بالحق في تعديل أو إلغاء الطلبات بسبب أخطاء التسعير.</p>

<h3>حسابات المستخدمين</h3>
<p>أنت مسؤول عن الحفاظ على سرية معلومات حسابك وكلمة المرور.</p>

<h3>حدود المسؤولية</h3>
<p>لن نكون مسؤولين عن أي أضرار مباشرة أو غير مباشرة أو عرضية أو تبعية ناشئة عن استخدامك للموقع.</p>',
                'label_en' => 'Terms of Service (Arabic)',
                'label_ar' => 'شروط الخدمة (عربي)',
                'description_en' => 'Terms of service content in Arabic',
                'description_ar' => 'محتوى شروط الخدمة باللغة العربية',
                'is_required' => true,
                'display_order' => 6,
            ],
            // Policy page visibility settings
            [
                'key' => 'show_privacy_policy',
                'group' => 'legal',
                'type' => 'boolean',
                'value' => '1',
                'label_en' => 'Show Privacy Policy Link',
                'label_ar' => 'إظهار رابط سياسة الخصوصية',
                'description_en' => 'Enable or disable the Privacy Policy link in footer',
                'description_ar' => 'تفعيل أو إلغاء رابط سياسة الخصوصية في التذييل',
                'is_required' => false,
                'display_order' => 7,
            ],
            [
                'key' => 'show_return_policy',
                'group' => 'legal',
                'type' => 'boolean',
                'value' => '1',
                'label_en' => 'Show Return Policy Link',
                'label_ar' => 'إظهار رابط سياسة الإرجاع',
                'description_en' => 'Enable or disable the Return Policy link in footer',
                'description_ar' => 'تفعيل أو إلغاء رابط سياسة الإرجاع في التذييل',
                'is_required' => false,
                'display_order' => 8,
            ],
            [
                'key' => 'show_terms_of_service',
                'group' => 'legal',
                'type' => 'boolean',
                'value' => '1',
                'label_en' => 'Show Terms of Service Link',
                'label_ar' => 'إظهار رابط شروط الخدمة',
                'description_en' => 'Enable or disable the Terms of Service link in footer',
                'description_ar' => 'تفعيل أو إلغاء رابط شروط الخدمة في التذييل',
                'is_required' => false,
                'display_order' => 9,
            ],
            // Contact page content settings
            [
                'key' => 'contact_page_en',
                'group' => 'legal',
                'type' => 'textarea',
                'value' => '<h2>Contact Us</h2>
<p>We\'d love to hear from you! Get in touch with us for any questions, concerns, or feedback.</p>

<h3>Get In Touch</h3>
<p>Feel free to reach out to us through any of the following methods:</p>

<h3>Customer Service</h3>
<p>Our customer service team is available to help you with orders, returns, and general inquiries.</p>

<h3>Business Hours</h3>
<p>Monday - Friday: 9:00 AM - 6:00 PM<br>
Saturday: 10:00 AM - 4:00 PM<br>
Sunday: Closed</p>

<h3>Response Time</h3>
<p>We typically respond to all inquiries within 24 hours during business days.</p>',
                'label_en' => 'Contact Page (English)',
                'label_ar' => 'صفحة الاتصال (إنجليزي)',
                'description_en' => 'Contact page content in English',
                'description_ar' => 'محتوى صفحة الاتصال باللغة الإنجليزية',
                'is_required' => true,
                'display_order' => 10,
            ],
            [
                'key' => 'contact_page_ar',
                'group' => 'legal',
                'type' => 'textarea',
                'value' => '<h2>اتصل بنا</h2>
<p>نحن نحب أن نسمع منك! تواصل معنا لأي أسئلة أو مخاوف أو ملاحظات.</p>

<h3>تواصل معنا</h3>
<p>لا تتردد في التواصل معنا من خلال أي من الطرق التالية:</p>

<h3>خدمة العملاء</h3>
<p>فريق خدمة العملاء متاح لمساعدتك في الطلبات والإرجاع والاستفسارات العامة.</p>

<h3>ساعات العمل</h3>
<p>الاثنين - الجمعة: 9:00 صباحاً - 6:00 مساءً<br>
السبت: 10:00 صباحاً - 4:00 مساءً<br>
الأحد: مغلق</p>

<h3>وقت الاستجابة</h3>
<p>عادة ما نرد على جميع الاستفسارات في غضون 24 ساعة خلال أيام العمل.</p>',
                'label_en' => 'Contact Page (Arabic)',
                'label_ar' => 'صفحة الاتصال (عربي)',
                'description_en' => 'Contact page content in Arabic',
                'description_ar' => 'محتوى صفحة الاتصال باللغة العربية',
                'is_required' => true,
                'display_order' => 11,
            ],
            [
                'key' => 'show_contact_page',
                'group' => 'legal',
                'type' => 'boolean',
                'value' => '1',
                'label_en' => 'Show Contact Page Link',
                'label_ar' => 'إظهار رابط صفحة الاتصال',
                'description_en' => 'Enable or disable the Contact page link in navigation',
                'description_ar' => 'تفعيل أو إلغاء رابط صفحة الاتصال في التنقل',
                'is_required' => false,
                'display_order' => 12,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
