<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Recruitment;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * 通知を作成
     */
    public static function create($userId, $type, $message, $data = null)
    {
        try {
            return Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'message' => $message,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('通知作成エラー: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 申請受信通知を作成
     */
    public static function applicationReceived($recruitmentId, $applicantName, $recruitmentTitle)
    {
        try {
            // 募集主に通知
            $recruitment = Recruitment::find($recruitmentId);
            if ($recruitment) {
                $notification = self::create(
                    $recruitment->user_id,
                    'application_received',
                    "「{$recruitmentTitle}」に「{$applicantName}」さんから申請が来ました。",
                    [
                        'recruitment_id' => $recruitmentId,
                        'applicant_name' => $applicantName,
                        'recruitment_title' => $recruitmentTitle,
                    ]
                );
                
                if ($notification) {
                    Log::info("申請受信通知を作成しました: ユーザーID {$recruitment->user_id}");
                }
                
                return $notification;
            } else {
                Log::error("募集が見つかりません: 募集ID {$recruitmentId}");
            }
        } catch (\Exception $e) {
            Log::error('申請受信通知作成エラー: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * 申請承認通知を作成
     */
    public static function applicationApproved($recruitmentId, $recruitmentTitle, $applicantId)
    {
        try {
            // 申請者に通知
            $notification = self::create(
                $applicantId,
                'application_approved',
                "「{$recruitmentTitle}」への申請が承認されました！",
                [
                    'recruitment_id' => $recruitmentId,
                    'recruitment_title' => $recruitmentTitle,
                ]
            );
            
            if ($notification) {
                Log::info("申請承認通知を作成しました: ユーザーID {$applicantId}");
            }
            
            return $notification;
        } catch (\Exception $e) {
            Log::error('申請承認通知作成エラー: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * 申請拒否通知を作成
     */
    public static function applicationRejected($recruitmentId, $recruitmentTitle, $applicantId)
    {
        try {
            // 申請者に通知
            $notification = self::create(
                $applicantId,
                'application_rejected',
                "「{$recruitmentTitle}」への申請が拒否されました。",
                [
                    'recruitment_id' => $recruitmentId,
                    'recruitment_title' => $recruitmentTitle,
                ]
            );
            
            if ($notification) {
                Log::info("申請拒否通知を作成しました: ユーザーID {$applicantId}");
            }
            
            return $notification;
        } catch (\Exception $e) {
            Log::error('申請拒否通知作成エラー: ' . $e->getMessage());
        }
        
        return null;
    }
}
