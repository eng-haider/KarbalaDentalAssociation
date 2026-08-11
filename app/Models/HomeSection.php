<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = ['key', 'name', 'sort_order', 'is_visible'];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('sort_order');
    }

    /**
     * The sections the home page can render, in their default order.
     *
     * Each key matches a `resources/views/components/site/<key>.blade.php`
     * component and a branch of the switch in `home.blade.php`, so this list is
     * the source of truth: a row whose key isn't here renders nothing.
     *
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'hero' => 'الشريط الرئيسي (السلايدر)',
            'transaction-search' => 'البحث عن معاملة',
            'clinic-analytics' => 'التحليل الإحصائي للعيادات',
            'featured-event' => 'الفعالية المميزة',
            'about' => 'عن النقابة',
            'news' => 'النشاطات',
            'board' => 'مجلس النقابة',
            'statistics' => 'النقابة بالأرقام',
            'courses' => 'الدورات التدريبية',
            'regulations' => 'الضوابط والشروط',
            'apply' => 'التقديم والتجديد',
            'discounts' => 'الخصومات والإعلانات',
            'jobs' => 'فرص العمل',
            'marketplace' => 'بيع وشراء',
            'partners' => 'شركاء النقابة',
            'social' => 'وسائل التواصل',
            'complaint' => 'إرسال شكوى',
            'contact' => 'اتصل بنا',
        ];
    }

    /**
     * Visible sections in admin order. Falls back to the default order the
     * first time the page is rendered on a database that has no rows yet.
     *
     * @return \Illuminate\Support\Collection<int, HomeSection>
     */
    public static function ordered(): \Illuminate\Support\Collection
    {
        $sections = static::visible()->get();

        if ($sections->isNotEmpty()) {
            return $sections;
        }

        return collect(static::defaults())
            ->map(fn (string $name, string $key): HomeSection => new static([
                'key' => $key,
                'name' => $name,
                'is_visible' => true,
            ]))
            ->values();
    }
}
