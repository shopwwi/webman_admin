<?php
/**
 *-------------------------------------------------------------------------s*
 * 用户信息表模型
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2022 Shopwwi Inc. (http://www.shopwwi.com)
 *-------------------------------------------------------------------------o*
 * @license    http://www.shopwwi.com        s h o p w w i . c o m
 *-------------------------------------------------------------------------p*
 * @link       http://www.shopwwi.com by 无锡豚豹科技
 *-------------------------------------------------------------------------w*
 * @since      ShopWWI智能管理系统
 *-------------------------------------------------------------------------w*
 * @author      8988354@qq.com TycoonSong
 *-------------------------------------------------------------------------i*
 */
namespace Shopwwi\Admin\App\User\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Shopwwi\Admin\App\User\Traits\UserTraits;
use Shopwwi\Admin\Libraries\NumberCast;
use Shopwwi\Admin\Libraries\Storage;

class Users extends Model
{
    use UserTraits;
    use SoftDeletes;
    // const CREATED_AT = 'created_at';
    // const UPDATED_AT = 'updated_at';

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * 与表关联的主键
     *
     * @var string
     */
    // protected $primaryKey = 'flight_id';

    /**
     * 主键是否主动递增
     *
     * @var bool
     */
    // public $incrementing = false;

    /**
     * 自动递增主键的「类型」
     *
     * @var string
     */
    // protected $keyType = 'string';

    /**
     * 是否主动维护时间戳
     *
     * @var bool
     */
    // public $timestamps = false;

    /**
     * 模型日期的存储格式
     *
     * @var string
     */
    // protected $dateFormat = 'U';

    /**
     * 模型的数据库连接名
     *
     * @var string
     */
    // protected $connection = 'connection-name';

    /**
     * 可批量赋值属性
     *
     * @var array
     */
    // protected $fillable = [];

    /**
     * 模型属性的默认值
     *
     * @var array
     */
    protected $attributes = [
        'status' => 1,
        'points' => 0,
        'growth' => 0,
        'available_balance' => 0,
        'frozen_balance' => 0,
        'phone_bind' => 0,
        'email_bind' => 0
    ];

    protected $casts = [
        'available_balance' => NumberCast::class,
        'frozen_balance' => NumberCast::class
    ];

    /**
     * 不可批量赋值的属性
     *
     * @var array
     */
    protected $guarded = [];
    protected $hidden = [
        'password','pay_pwd'
    ];

    protected $appends=['avatarUrl'];

//    public function getLabelAttribute($key)
//    {
//        if(empty($key)) return $key;
//        $items = explode(',',$key);
//        $numbers = [];
//        foreach ($items as $number){
//            $numbers[] = intval($number);
//        }
//        return $numbers;
//    }

    /**
     * @param $key
     */
    public function getAvatarUrlAttribute($key)
    {
        return empty($this->avatar)? '//api.multiavatar.com/'.$this->nickname.'.png': Storage::url($this->avatar);
    }

    public function getBirthdayAttribute($key)
    {
        return empty($key)?$key:Carbon::parse($key)->format('Y-m-d');
    }
}
