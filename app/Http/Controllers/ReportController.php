<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request)
    {try{
        $request->validate([
            'reported_id' => 'required|integer|exists:comments,id',
            'content' => 'required|string|max:1000',
        ]);

        $report =    Report::create([
            'reporter_id' => Auth::id(),
            'reported_id' => $request->reported_id,
            'report_table' => 'comments',
            'content' => $request->content,
            'status' => 'pending',
        ]);

        $user = Auth::user(); 



// 2.  build the full friendly message
$fullMessage = <<<EOT
عزيزي/عزيزتنا {$user->name},

نود أن نشكرك على إبلاغنا عن هذا الأمر. لقد تم استلام إبلاغك بنجاح، ونحن نقدر مجهودك في مساعدتنا على تحسين خدماتنا والحفاظ على بيئة آمنة للجميع.

فريقنا المختص يقوم حاليًا بمراجعة الإبلاغ الذي قدمته. سنقوم بتحليل المعلومات المقدمة واتخاذ الإجراءات اللازمة بناءً على ذلك. نحرص دائمًا على التعامل بجدية مع جميع الإبلاغات لضمان تحقيق أفضل النتائج.

سيتم إرسال تحديثات حول حالة الإبلاغ إلى عنوان البريد الإلكتروني المرتبط بحسابك ({$user->email}). يُرجى التأكد من التحقق من صندوق الوارد الخاص بك بانتظام للحصول على أي إشعارات أو تحديثات.

إذا كانت لديك أي معلومات إضافية أو مستندات تدعم إبلاغك، فلا تتردد في الرد على هذه الرسالة أو التواصل معنا مباشرة. فريق الدعم لدينا سيكون سعيدًا بمساعدتك.

نحن ندرك أهمية إبلاغك، ونسعى جاهدين لتقديم استجابة سريعة وفعالة. يُرجى ملاحظة أن أوقات الاستجابة قد تختلف اعتمادًا على تعقيد القضية المطروحة، ولكننا نضمن لك أننا نعمل بجد لمعالجة الأمر في أقرب وقت ممكن.

شكرًا لصبرك وتفهمك.

مع خالص التحية،
فريق الدعم
EOT;

// 3.  insert into custom notifications table
Notification::create([
    'owner_id' => auth::id(),
    'title'    => 'تم استلام إبلاغك',
    'content'  => $fullMessage,
    'sender'   => 'system',
]);


        return response()->json([
            'message' => 'تم إرسال البلاغ بنجاح، وسنقوم بمراجعته.'
        ], 201);
    }catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong while sending report.',
            'error_message' => $e->getMessage(),
        ], 500);
    }
    }
}