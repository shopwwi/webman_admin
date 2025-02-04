<?php
/**
 *-------------------------------------------------------------------------s*
 * 参数配置控制器
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
namespace Shopwwi\Admin\App\Admin\Controllers\System;

use Shopwwi\Admin\App\Admin\Service\AmisService;
use Shopwwi\Admin\App\Admin\Service\DictTypeService;
use Shopwwi\Admin\App\Admin\Service\SysConfigService;
use Shopwwi\Admin\Libraries\Amis\AdminController;
use Shopwwi\Admin\Libraries\Storage;
use Shopwwi\Admin\Libraries\Validator;
use support\Request;

class SysConfigController extends AdminController
{
    public  $model = \Shopwwi\Admin\App\Admin\Models\SysConfig::class;
    protected $trans = 'sysConfig'; // 语言文件名称
    protected $queryPath = 'system/config'; // 完整路由地址
    protected $activeKey = 'settingSystemConfig';
    protected $useHasRecovery = true;

    public $routePath = 'config'; // 当前路由模块不填写则直接控制器名
    public  $orderBy = ['id' => 'desc'];
    protected $adminOp = true;
    /**
     * 数据字段处理
     * @return array
     */
    protected function fields(){
        $yesOrNo = DictTypeService::getAmisDictType('yesOrNo');
        return [
            shopwwiAmisFields(trans('field.id',[],'messages'),'id')->tableColumn(['width'=>60,'sortable'=>true])->showOnUpdate(0)->showOnCreation(0)->showFilter(),
            shopwwiAmisFields(trans('field.name',[],'sysConfig'),'name')->rules('required')->showFilter(),
            shopwwiAmisFields(trans('field.key',[],'sysConfig'),'key')->rules('required'),
            shopwwiAmisFields(trans('field.value',[],'sysConfig'),'value')->showOnIndex(2)->column('json-editor',['md'=>12]),
            shopwwiAmisFields(trans('field.is_system',[],'sysConfig'),'is_system')->tableColumn(['sortable'=>true,'type'=>'mapping','map'=>$this->toMappingSelect($yesOrNo,'${is_system}')])
                ->filterColumn('select',['options'=>$yesOrNo])
                ->column('switch',['trueValue'=>1,'falseValue'=>0]),
            shopwwiAmisFields(trans('field.is_open',[],'sysConfig'),'is_open')->tableColumn(['sortable'=>true,'type'=>'mapping','map'=>$this->toMappingSelect($yesOrNo,'${is_open}')])
                ->filterColumn('select',['options'=>$yesOrNo])
                ->column('switch',['trueValue'=>1,'falseValue'=>0]),
            shopwwiAmisFields(trans('field.remark',[],'sysConfig'),'remark')->column('textarea',['md'=>12]),
            shopwwiAmisFields(trans('field.created_user_id',[],'messages'),'created_user_id')->showOnUpdate(0)->showOnCreation(0)->tableColumn(['width'=>60,'toggled'=>false,'sortable'=>true])->filterColumn('picker',['joinValues'=>true,'source'=>shopwwiAdminUrl('system/user/list?_format=json'),'size'=>'lg','valueField'=>'id','labelField'=>'nickname','pickerSchema'=>AmisService::getSysUserList()]),
            shopwwiAmisFields(trans('field.created_at',[],'messages'),'created_at')->tableColumn(['width'=>145,'sortable'=>true])->filterColumn('input-datetime-range',['format'=> 'YYYY-MM-DD HH:mm:ss','name'=>'created_at_betweenAmisTime'])->showOnUpdate(0)->showOnCreation(0),
            shopwwiAmisFields(trans('field.updated_user_id',[],'messages'),'updated_user_id')->showOnUpdate(0)->showOnCreation(0)->tableColumn(['width'=>60,'toggled'=>false,'sortable'=>true])->filterColumn('picker',['joinValues'=>true,'source'=>shopwwiAdminUrl('system/user/list?_format=json'),'size'=>'lg','valueField'=>'id','labelField'=>'nickname','pickerSchema'=>AmisService::getSysUserList()]),
            shopwwiAmisFields(trans('field.updated_at',[],'messages'),'updated_at')->tableColumn(['width'=>145,'toggled'=>false,'sortable'=>true])->filterColumn('input-datetime-range',['format'=> 'YYYY-MM-DD HH:mm:ss','name'=>'updated_at_betweenAmisTime'])->showOnUpdate(0)->showOnCreation(0),
            shopwwiAmisFields(trans('field.deleted_at',[],'messages'),'deleted_at')->tableColumn(['width'=>145,'sortable'=>true])->showOnUpdate(0)->showOnCreation(0)->showOnIndex(4)->filterColumn('input-datetime-range',['format'=> 'YYYY-MM-DD HH:mm:ss','name'=>'deleted_at_betweenAmisTime']),
        ];
    }
    protected function beforeStore($user,&$validator){
        $res =  $this->filterStore();
        $validator = Validator::make(\request()->all(), $res['rule'], [], $res['lang']);
        $params = shopwwiParams($res['filter']); //指定字段
        if(isset($params['value']) && is_string($params['value'])){
            $params['value'] = json_decode(trim($params['value']),true);
        }
        return $params;
    }

    protected function insertUpdating($user,$params,&$info,$oldInfo){
        if(isset($info->value) && is_string($info->value)){
            $info->value = json_decode(trim($info->value),true);
        }
    }

    /**
     * 第三方登入
     * @param Request $request
     * @return \support\Response|void
     */
    public function auth(Request $request)
    {
        try {
            if($request->method() === 'GET'){
                if($this->format() == 'json'){
                    $info = SysConfigService::getFirstOrCreate([
                        'key' => 'socialite'
                    ],['name'=>'登入接口信息','value'=>[
                        'qq' => ['client_id'=>'','client_secret'=>'','redirect'=>'{$userUrl}/auth/qq/callback'],
                        'wechat' => ['client_id'=>'','client_secret'=>'','component'=>['id'=>'','token'=>''],'redirect'=>'{$userUrl}/auth/wechat/callback'],
                        'weibo' => ['client_id'=>'','client_secret'=>'','redirect'=>'{$userUrl}/auth/weibo/callback'],
                        'taobao' => ['client_id'=>'','client_secret'=>'','redirect'=>'{$userUrl}/auth/taobao/callback'],
                        'alipay' => ['client_id'=>'','rsa_private_key'=>'','redirect'=>'{$userUrl}/auth/alipay/callback'],
                        'coding' => ['client_id'=>'','client_secret'=>'','team_url'=>'https://{your-team}.coding.net', 'redirect'=>'{$userUrl}/auth/coding/callback'],
                        'dingtalk' => ['client_id'=>'','client_secret'=>'', 'redirect'=>'{$userUrl}/auth/dingtalk/callback'],
                        'baidu' => ['client_id'=>'','client_secret'=>'', 'redirect'=>'{$userUrl}/auth/baidu/callback'],
                        'azure' => ['client_id'=>'','client_secret'=>'', 'redirect'=>'{$userUrl}/auth/azure/callback'],
                        'douban' => ['client_id'=>'','client_secret'=>'', 'redirect'=>'{$userUrl}/auth/douban/callback'],
                        'facebook' => ['client_id'=>'','client_secret'=>'', 'redirect'=>'{$userUrl}/auth/facebook/callback'],
                        'feishu' => ['client_id'=>'','client_secret'=>'', 'redirect'=>'{$userUrl}/auth/feishu/callback'],
                        'figma' => ['client_id'=>'','client_secret'=>'', 'redirect'=>'{$userUrl}/auth/figma/callback'],
                        'gitee' => ['client_id'=>'','client_secret'=>'', 'redirect'=>'{$userUrl}/auth/gitee/callback'],
                        'github' => ['client_id'=>'','client_secret'=>'', 'redirect'=>'{$userUrl}/auth/github/callback'],
                        'toutiao' => ['client_id'=>'','client_secret'=>'', 'redirect'=>'{$userUrl}/auth/toutiao/callback'],
                        'wework' => ['client_id'=>'','client_secret'=>'', 'redirect'=>'{$userUrl}/auth/wework/callback'],
                        'xigua' => ['client_id'=>'','client_secret'=>'', 'redirect'=>'{$userUrl}/auth/xigua/callback'],
                    ]]);
                    return shopwwiSuccess($info);
                }
                $form = $this->baseForm()->body([
                    shopwwiAmis('grid')->gap('lg')->columns([
                        shopwwiAmis('json-editor')->label('登入配置')->name('socialite')->placeholder('请输入登入配置')->xs(12),
                    ])
                ])->api('post:' . shopwwiAdminUrl('system/config/auth'))
                    ->actions([
                        shopwwiAmis('reset')->label(trans('reset',[],'messages')),
                        shopwwiAmis('submit')->label(trans('submit',[],'messages'))->level('primary')
                    ])
                    ->initApi(shopwwiAdminUrl('system/config/auth?_format=json'));
                $page = $this->basePage()->body($form);
                $page = $page->subTitle('登入配置');
                if($this->format() == 'data' || $this->format() == 'web') return shopwwiSuccess($page);
                return $this->getAdminView(['json'=>$page,'activeKey'=>'settingWayAuth']);
            }else{
                $params = shopwwiParams(['socialite']);
                SysConfigService::updateSetting($params);
            }
            return shopwwiSuccess();
        }catch (\Exception $e){
            return shopwwiError($e->getMessage());
        }
    }

    /**
     * 站点信息配置
     * @param Request $request
     * @return \support\Response
     */
    public function site(Request $request)
    {
        try {
            if($request->method() === 'GET'){
                if($this->format() == 'json'){
                    $info = SysConfigService::getFirstOrCreate([
                        'key' => 'siteInfo'
                    ],['name'=>'站点信息','value'=>[
                        'siteName' => 'ShopWWI智能管理系统',
                        'siteUrl' => '',
                        'siteIcp' => '',
                        'siteBol' => '',
                        'sitePoliceNet' => '',
                        'siteLogo' => '',
                        'siteIcon' => '',
                        'siteKeyword' => '',
                        'siteDescription' => '',
                        'siteStatus' => '0',
                        'siteCloseRemark' => '',
                        'siteEmail' => '',
                        'sitePhone' => '',
                        'siteFlowCode' => ''
                    ]]);
                    $info['siteInfo']['siteLogoUrl'] = Storage::url($info['siteInfo']['siteLogo'] ?? '');
                    $info['siteInfo']['siteIconUrl'] = Storage::url($info['siteInfo']['siteIcon'] ?? '');
                    return shopwwiSuccess($info);
                }
                $openOrClose = DictTypeService::getAmisDictType('openOrClose');
                $form = $this->baseForm()->body([
                    shopwwiAmis('grid')->gap('lg')->columns([
                        shopwwiAmis('input-text')->name('siteInfo.siteName')->label(trans('siteInfo.siteName',[],$this->trans))->placeholder(trans('form.input',['attribute'=>trans('siteInfo.siteName',[],$this->trans)],'messages'))->xs(12),
                        shopwwiAmis('input-text')->name('siteInfo.siteUrl')->label('站点链接')->placeholder('请输入站点链接')->xs(12),
                        shopwwiAmis('combo')->name('siteInfo')->label('')->items([
                            shopwwiAmis('hidden')->name('siteLogo'),
                            shopwwiAmis('hidden')->name('siteIcon'),
                            shopwwiAmis('input-image')->name('siteLogoUrl')->label(trans('siteInfo.siteLogo',[],$this->trans))->xs(12)->md(6)->autoFill(['siteLogo'=>'${file_name}'])->initAutoFill(false)->receiver(shopwwiAdminUrl('common/upload')),
                            shopwwiAmis('input-image')->name('siteIconUrl')->label(trans('siteInfo.siteIcon',[],$this->trans))->xs(12)->md(6)->autoFill(['siteIcon'=>'${file_name}'])->initAutoFill(false)->receiver(shopwwiAdminUrl('common/upload')),
                        ]),
                        shopwwiAmis('input-text')->name('siteInfo.siteEmail')->label(trans('siteInfo.siteEmail',[],$this->trans))->placeholder(trans('form.input',['attribute'=>trans('siteInfo.siteEmail',[],$this->trans)],'messages'))->xs(12),
                        shopwwiAmis('input-text')->name('siteInfo.sitePhone')->label(trans('siteInfo.sitePhone',[],$this->trans))->placeholder(trans('form.input',['attribute'=>trans('siteInfo.sitePhone',[],$this->trans)],'messages'))->xs(12),
                        shopwwiAmis('input-text')->name('siteInfo.siteIcp')->label(trans('siteInfo.siteIcp',[],$this->trans))->placeholder(trans('form.input',['attribute'=>trans('siteInfo.siteIcp',[],$this->trans)],'messages'))->xs(12),
                        shopwwiAmis('input-text')->name('siteInfo.siteBol')->label(trans('siteInfo.siteBol',[],$this->trans))->placeholder(trans('form.input',['attribute'=>trans('siteInfo.siteBol',[],$this->trans)],'messages'))->xs(12),
                        shopwwiAmis('input-text')->name('siteInfo.sitePoliceNet')->label(trans('siteInfo.sitePoliceNet',[],$this->trans))->placeholder(trans('form.input',['attribute'=>trans('siteInfo.sitePoliceNet',[],$this->trans)],'messages'))->xs(12),

                        shopwwiAmis('input-text')->name('siteInfo.siteKeyword')->label(trans('siteInfo.siteKeyword',[],$this->trans))->placeholder(trans('form.input',['attribute'=>trans('siteInfo.siteKeyword',[],$this->trans)],'messages'))->xs(12),
                        shopwwiAmis('textarea')->name('siteInfo.siteDescription')->label(trans('siteInfo.siteDescription',[],$this->trans))->placeholder(trans('form.input',['attribute'=>trans('siteInfo.siteDescription',[],$this->trans)],'messages'))->xs(12),
                        shopwwiAmis('textarea')->name('siteInfo.siteFlowCode')->label(trans('siteInfo.siteFlowCode',[],$this->trans))->placeholder(trans('form.input',['attribute'=>trans('siteInfo.siteFlowCode',[],$this->trans)],'messages'))->xs(12),
                        shopwwiAmis('radios')->name('siteInfo.siteStatus')->label(trans('siteInfo.siteStatus',[],$this->trans))->selectFirst(true)->options($openOrClose)->xs(12),
                        shopwwiAmis('textarea')->name('siteInfo.siteCloseRemark')->label(trans('siteInfo.siteCloseRemark',[],$this->trans))->placeholder(trans('form.input',['attribute'=>trans('siteInfo.siteCloseRemark',[],$this->trans)],'messages'))->xs(12)->visibleOn('this.siteInfo?.siteStatus != 1'),

                    ])
                ])->api('post:' . shopwwiAdminUrl('system/config/site'))
                    ->actions([
                        shopwwiAmis('reset')->label(trans('reset',[],'messages')),
                        shopwwiAmis('submit')->label(trans('submit',[],'messages'))->level('primary')
                    ])
                    ->initApi(shopwwiAdminUrl('system/config/site?_format=json'));
                $page = $this->basePage()->body($form);
                $page = $page->subTitle(trans('siteInfo.title',[],$this->trans));
                if($this->format() == 'data' || $this->format() == 'web') return shopwwiSuccess($page);
                return $this->getAdminView(['json'=>$page,'activeKey'=>'settingSiteBase']);
            }else{
                $params = shopwwiParams(['siteInfo']);
                SysConfigService::updateSetting($params);
            }
            return shopwwiSuccess();
        }catch (\Exception $e){
            return shopwwiError($e->getMessage());
        }
    }

    /**
     * 站点规则配置
     * @param Request $request
     * @return \support\Response
     */
    public function rule(Request $request)
    {
        try {
            if($request->method() === 'GET'){
                if($this->format() == 'json'){
                    $info = SysConfigService::getFirstOrCreate([
                        'key' => 'userLoginImages'
                    ],['name'=>'站点规则设置','value'=>[]]);
                    return shopwwiSuccess($info);
                }
                $form = $this->baseForm()->body([
                    shopwwiAmis('grid')->gap('lg')->columns([
                        shopwwiAmis('combo')->name('userLoginImages')->label('登入图片')->multiple(true)->items([
                            shopwwiAmis('hidden')->name('imageName'),
                            shopwwiAmis('input-image')->name('image')->autoFill(['imageName'=>'${file_name}'])->initAutoFill(false)->receiver(shopwwiAdminUrl('common/upload'))->label('登入图片'),
                            shopwwiAmis('input-color')->name('bgColor')->label('背景颜色'),
                        ])
                    ])
                ])->api('post:' . shopwwiAdminUrl('system/config/rule'))
                    ->actions([
                        shopwwiAmis('reset')->label(trans('reset',[],'messages')),
                        shopwwiAmis('submit')->label(trans('submit',[],'messages'))->level('primary')
                    ])
                    ->initApi(shopwwiAdminUrl('system/config/rule?_format=json'));
                $page = $this->basePage()->body($form);
                $page = $page->subTitle(trans('siteInfo.title',[],$this->trans));
                if($this->format() == 'data' || $this->format() == 'web') return shopwwiSuccess($page);
                return $this->getAdminView(['json'=>$page,'activeKey'=>'settingSiteBaseRule']);
            }else{
                $params = shopwwiParams(['userLoginImages']);
                SysConfigService::updateSetting($params);
            }
            return shopwwiSuccess();
        }catch (\Exception $e){
            return shopwwiError($e->getMessage());
        }
    }

    public function pic(Request $request)
    {
        try {
            if($request->method() === 'GET'){
                if($this->format() == 'json'){
                    $info = SysConfigService::getFirstOrCreate([
                        'key' => 'siteDefaultImage'
                    ],['name'=>'站点默认图片','value'=>[
                        'noPic' => 'uploads/default_image.png',
                        'userAvatar' => 'uploads/default_avatar.png',
                    ]]);
                    $info['siteDefaultImage']['noPicUrl'] = Storage::url($info['siteDefaultImage']['noPic'] ?? '');
                    $info['siteDefaultImage']['userAvatarUrl'] = Storage::url($info['siteDefaultImage']['userAvatar'] ?? '');
                    return shopwwiSuccess($info);
                }
                $form = $this->baseForm()->body([
                    shopwwiAmis('grid')->gap('lg')->columns([
                        shopwwiAmis('combo')->name('siteDefaultImage')->label('')->items([
                            shopwwiAmis('hidden')->name('noPic'),
                            shopwwiAmis('hidden')->name('userAvatar'),
                            shopwwiAmis('input-image')->name('noPicUrl')->label(trans('siteDefaultImage.goodsImage',[],$this->trans))->xs(12)->md(6)->autoFill(['noPic'=>'${file_name}'])->initAutoFill(false)->receiver(shopwwiAdminUrl('common/upload')),
                            shopwwiAmis('input-image')->name('userAvatarUrl')->label(trans('siteDefaultImage.userImage',[],$this->trans))->xs(12)->md(6)->autoFill(['userAvatar'=>'${file_name}'])->initAutoFill(false)->receiver(shopwwiAdminUrl('common/upload')),
                        ])
                    ])
                ])->api('post:' . shopwwiAdminUrl('system/config/pic'))
                    ->actions([
                        shopwwiAmis('reset')->label(trans('reset',[],'messages')),
                        shopwwiAmis('submit')->label(trans('submit',[],'messages'))->level('primary')
                    ])
                    ->initApi(shopwwiAdminUrl('system/config/pic?_format=json'));
                $page = $this->basePage()->body($form);
                $page = $page->subTitle('默认图片设置');
                if($this->format() == 'data' || $this->format() == 'web') return shopwwiSuccess($page);
                return $this->getAdminView(['json'=>$page,'activeKey'=>'settingSiteBasePic']);
            }else{
                $params = shopwwiParams(['siteDefaultImage']);
                SysConfigService::updateSetting($params);
            }
            return shopwwiSuccess();
        }catch (\Exception $e){
            return shopwwiError($e->getMessage());
        }
    }

    /**
     * 签到奖励
     * @param Request $request
     * @return \support\Response
     */
    public function signing(Request $request)
    {
        try {
            if($request->method() === 'GET'){
                $info = SysConfigService::getFirstOrCreate([
                    'key' => 'signing'
                ],['name'=>'签到规则','value'=>[
                    'used' => '1', // 签到状态
                    'days' =>  30, //签到周期
                    'points' => 10, // 日常积分
                    'growth' => 0, // 日常成长值
                    'list' => [
                        ['day' => 2, 'points' => 15 , 'growth' => 0],
                        ['day' => 4, 'points' => 20 , 'growth' => 0]
                    ],
                ]]);
                return shopwwiSuccess($info);
            }else{
                $params = shopwwiParams(['signing']);
                SysConfigService::updateSetting($params);
                return shopwwiSuccess();
            }
        }catch (\Exception $E){
            return shopwwiError($E->getMessage());
        }
    }
}
