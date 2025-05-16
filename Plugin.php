<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * CommentDing
 * 
 * @package CommentDing 
 * @author CatiZ
 * @version 1.0.0
 * @link https://www.catiz.cn
 */
class CommentDing_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Feedback')->finishComment = array('CommentDing_Plugin', 'post');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        /** 分类名称 */
        
        $url = new Typecho_Widget_Helper_Form_Element_Text('url', NULL, NULL, _t('钉钉URL'));
        $url->description(_t('请将服务器IP添加至IP白名单'));
        $form->addInput($url->addRule('required', _t('您必须填写一个正确的 URL地址')));
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
    public static function post($comment)
    {
        
        if($comment->authorId != $comment->ownerId){
        
        $options = Typecho_Widget::widget('Widget_Options');

        $url = $options->plugin('CommentDing')->url;
        
        $postdata=array();
        $commentAt = new Typecho_Date($comment->created);
        $commentAt = $commentAt->format('Y-m-d H:i:s');
        $postdata=[
    "msgtype" =>"actionCard",
    "actionCard" => [
        "title" =>"评论提醒",
        "text" =>"#### 评论提醒 \n\n> 您的文章 [".$comment->title."](dingtalk://dingtalkclient/page/link?url=".$options->siteUrl."/".$comment->cid.".html&pc_slide=true) 有了新评论 \n\n > [".$comment->author."](dingtalk://dingtalkclient/page/link?url=".$comment->url."&pc_slide=true)：".$comment->text."\n\n > ###### 评论时间：".$commentAt,
        "btnOrientation" =>"0",
        "btns" =>[
            [
                "title" =>"查看评论",
                "actionURL" =>$comment->permalink
            ]
        ]
    ]
];
        $jsonStr = json_encode($postdata);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json;',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        }
        return  $comment;
    }
}
