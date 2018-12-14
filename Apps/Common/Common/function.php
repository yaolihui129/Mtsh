<?php

    //手机端模板
    function ismobile() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
            return true;
        //此条摘自TPM智能切换模板引擎，适合TPM开发
        if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
            return true;
        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA']))
            //找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        //判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
            );
            //从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        //协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    /**
     ** 数据库操作
     */
    function insert($table,$data){
        $_POST=$data;
        $user=getLoginUser();
        $_POST['adder'] = $user;
        $_POST['moder'] = $user;
        $_POST['ctime'] = time();
        $m = D($table);
        $m -> create();
        $id=$m -> add();
        return $id;
    }
    function update($table,$data){
        $_POST=$data;
        $user=getLoginUser();
        $_POST['moder'] = $user;
        $info=D($table)->save($_POST);
        return $info;
    }
    function del($table,$arrId){
        $user=getLoginUser();
        $info='';
        if(is_array($arrId)){
            foreach ($arrId as $vo){
                $_POST['id'] = $vo;
                $_POST['moder'] = $user;
                $_POST['deleted'] = 1;
                $info[]=D($table)->save($_POST);
            }
        }else{
            $_POST['id'] = $arrId;
            $_POST['moder'] = $user;
            $_POST['deleted'] = 1;
            $info=D($table)->save($_POST);
        }
        return $info;
    }
    function realDelete($table,$arrId)
    {
        $count = D($table)->delete($arrId);
        return $count;
    }
    function getList($table,$where,$order = 'id', $field = '',$page='',$size=''){
        $where['deleted'] = '0';
        if($page){
            $data = M($table)->where($where)->order($order)->field($field)->page($page,$size)->select();
        }else{
            $data = M($table)->where($where)->order($order)->field($field)->select();
        }
        return $data;
    }
    function find($table,$id,$field = ''){
        $data = M($table)->field($field)->find($id);
        return $data;
    }
    function findOne($table, $where, $order = 'id desc', $field = ''){
        $where['deleted'] = '0';
        $data = M($table)->where($where)->order($order)->field($field)->find();
        return $data;
    }
    function countId($table,$where){
        $where['deleted']='0';
        $count=M($table)->where($where)->count();
        return $count;
    }
    function getId($table, $where)
    {
        $data = M($table)->where($where)->find();
        return $data['id'];
    }
    function getName($table,$id,$name='name'){
        $data=M($table)->find($id);
        if($data[$name]){
            return $data[$name];
        }else{
            return $id;
        }
    }

    /**
     **模板输出
     */
    function baseGetList($assign,$table,$where,$order = 'id', $field = '',$page='',$size=''){
        $data=getList($table,$where,$order, $field,$page,$size);
        $this->assign($assign, $data);
        return true;
    }
    /**
     **对象输出
     */
    function baseUpdata($init_table,$data){
        //初始化
        $info = $this->init();
        if(I('id')){
            $res=$this->update($info[$init_table],$data);
        }else{
            $res=$this->insert($info[$init_table],$data);
        }
        return $res;
    }
    function baseDelete($init_table,$id){
        //初始化
        $info = $this->init();
        $res=$this->delete($info[$init_table],$id);
        return $res;
    }
    function baseGetInfo($init_table,$id){
        //初始化
        $info = $this->init();
        $data=find($info[$init_table],$id);
        if($data){
            $res=array(
                'errorcode'=>'0',
                'message'=>'ok',
                'result'=>$data
            );
        }else{
            $res=array(
                'errorcode'=>'0',
                'message'=>'ok'
            );
        }
        $this->ajaxReturn($res);
    }


    /**
     ** 加解密操作
     */
    function passport_encrypt($txt, $key = 'xiuliguanggao.com')
    {
        srand((double)microtime() * 1000000);
        $encrypt_key = md5(rand(0, 32000));
        $ctr = 0;
        $tmp = '';
        for($i = 0;$i < strlen($txt); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
        }
        return urlencode(base64_encode(passport_key($tmp, $key)));
    }
    function passport_decrypt($txt, $key = 'xiuliguanggao.com')
    {
        $txt = passport_key(base64_decode(urldecode($txt)), $key);
        $tmp = '';
        for($i = 0;$i < strlen($txt); $i++) {
            $md5 = $txt[$i];
            $tmp .= $txt[++$i] ^ $md5;
        }
        return $tmp;
    }
    function passport_key($txt, $encrypt_key)
    {
        $encrypt_key = md5($encrypt_key);
        $ctr = 0;
        $tmp = '';
        for($i = 0; $i < strlen($txt); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
        }
        return $tmp;
    }

    function jia_mi($data,$key='',$type='1'){
        if(!$key){
            $key=C(PRODUCT);
        }
        $data=lock_url($data,$key,$type);
        return $data;
    }
    function jie_mi($data,$key=''){
        if(!$key){
            $key=C(PRODUCT);
        }
        $data=unlock_url($data,$key);
        return $data;
    }
    //加密函数，$type='1',可变密文；$type='0',不变密文
    function lock_url($txt,$key,$type='0')
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+";
        if($type){
            $nh = rand(0,64);
        }else{
            $nh = 5;
        }
        $ch = $chars[$nh];
        $mdKey = md5($key.$ch);
        $mdKey = substr($mdKey,$nh%8, $nh%8+7);
        $txt = base64_encode($txt);
        $tmp = '';
        $i=0;$j=0;$k = 0;
        for ($i=0; $i<strlen($txt); $i++) {
            $k = $k == strlen($mdKey) ? 0 : $k;
            $j = ($nh+strpos($chars,$txt[$i])+ord($mdKey[$k++]))%64;
            $tmp .= $chars[$j];
        }
        return urlencode($ch.$tmp);
    }
    //解密函数
    function unlock_url($txt,$key)
    {
        $txt = urldecode($txt);
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+";
        $ch = $txt[0];
        $nh = strpos($chars,$ch);
        $mdKey = md5($key.$ch);
        $mdKey = substr($mdKey,$nh%8, $nh%8+7);
        $txt = substr($txt,1);
        $tmp = '';
        $i=0;$j=0; $k = 0;
        for ($i=0; $i<strlen($txt); $i++) {
            $k = $k == strlen($mdKey) ? 0 : $k;
            $j = strpos($chars,$txt[$i])-$nh - ord($mdKey[$k++]);
            while ($j<0) $j+=64;
            $tmp .= $chars[$j];
        }
        return base64_decode($tmp);
    }


    //根据pid获取分类数
    function countCate($pidCateId){
        $where=array('pidCateId'=>$pidCateId);
        $data=M('tp_cate')->where($where)->count();
        return $data;
    }
    //根据ModuleId获取功能信息
    function getParentModule($id){
        $data=M("module")->find($id);
        if($data['parent']){
            $str=getModuleName($data['parent']).'-';
        }else{
            $str='';
        }
        return $str;
    }
    //根据$pathid获取模块名
    function getModuleName($pathid){
        $data=M('module')->find($pathid);
        if ($data['parent']){
            $str=getModuleName($data['parent']).'-'.$data['name'];
        }else {
            $str=$data['name'];
        }
        return $str;
    }
    //获取分类名字
    function getCatname($cateid){
        if ($cateid){
            $data=M('tp_cate')->find($cateid);
            $str=getCatname($data['pidcateid'])."-".$data['catname'];
            return $str;
        }else {
            return "|-";
        }
    }
    //获取父级分类ID
    function getCatePid($cateId){
        $data=M('tp_cate')->find($cateId);
        return $data['pidcateid'];
    }





    //获取页面信息
    function getWebInfo($qz){
        $data=M('xl_web')->where(array('qz'=>$qz))->field('id,web,adress,desc,phone,tel,qq,qz,url,record')->find();
        $_SESSION[$qz]=$data;
        $_SESSION['ip']=get_client_ip();
        $_SESSION['browser']=GetBrowser();
        $_SESSION['os']=GetOs();
    }



    //登录
    function login($phone,$password){
        $where=array('phone'=>$phone,['password']=>md5($password));
        $data=M('tp_credit')->where($where)->find();
        if ($data){
            $_SESSION['isCLogin']= C(PRODUCT);
            $_SESSION['realname']= $data['realname'];
            $m=M('tp_customer');
            $where=array('creditid'=>$data['creditid'],'prodid'=>C(PRODID));
            $arr=$m->where($where)->find();
            if($arr){
            $_SESSION['userid'] =   $arr['id'];
            $_POST['id']        =   $arr['id'];
            $_POST['lastLoginTime']=date("Y-m-d H:i:s",time());
            $_POST['lastLoginIP']=get_client_ip();
            $m->save($_POST);//更新最后登录信息
            }else {
            $_POST['prodid']=C(PRODID);
            $_POST['creditid']=$data['id'];
            $_POST['name']=$data['realname'];
            $_POST['type']=0;
            $_POST['lastLoginTime']=date("Y-m-d H:i:s",time());
            $_POST['lastLoginIP']=get_client_ip();
            $_POST['adder']='客户登录';
            $_POST['moder']='客户登录';
            $_POST['ctime']=time();
            $m->create();
            $_SESSION['userid']=$m->add();
        }
            return $data;
        }else{
            return 0;
        }
    }
    //注销
    function logout(){
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(),'',time()-3600,'/');
        }
        session_destroy();// 销毁sesstion
    }


    //获得访客浏览器类型
    function GetBrowser(){
        if(!empty($_SERVER['HTTP_USER_AGENT'])){
            $br = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/MSIE/i',$br)) {
                $br = 'MSIE';
            }elseif (preg_match('/Firefox/i',$br)) {
                $br = 'Firefox';
            }elseif (preg_match('/Chrome/i',$br)) {
                $br = 'Chrome';
            }elseif (preg_match('/Safari/i',$br)) {
                $br = 'Safari';
            }elseif (preg_match('/Opera/i',$br)) {
                $br = 'Opera';
            }else {
                $br = 'Other';
            }
            return $br;
        }else{
            return "获取浏览器信息失败！";
        }
    }
    //是否为微信
    function is_weixin() {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        } return false;
    }
    //是否为QQ
    function is_qq() {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'QQ') !== false) {
            return true;
        } return false;
    }
    //是否为支付宝
    function is_alipay() {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'AlipayClient') !== false) {
            return true;
        } return false;
    }
    //获取访客操作系统
    function GetOs(){
        if(!empty($_SERVER['HTTP_USER_AGENT'])){
            $OS = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/win/i',$OS)) {
                $OS = 'Windows';
            }elseif (preg_match('/mac/i',$OS)) {
                $OS = 'MAC';
            }elseif (preg_match('/linux/i',$OS)) {
                $OS = 'Linux';
            }elseif (preg_match('/unix/i',$OS)) {
                $OS = 'Unix';
            }elseif (preg_match('/bsd/i',$OS)) {
                $OS = 'BSD';
            }else {
                $OS = 'Other';
            }
            return $OS;
        }else{
            return "获取访客操作系统信息失败！";
        }
    }
    //获取访客IP地址
    function GetIP(){
        //strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
        return $res;
    }
    // 服务器端IP
    function serverIP(){
        return gethostbyname($_SERVER["SERVER_NAME"]);
    }
    /**
     * 当前请求是否是https
     **/
    function is_https()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off';
    }

    /**
     **数组相关操作
     * @return string
     */
    /**
     * 二维数组排序
     * @param $arr
     * @param $keys
     * @param string $type
     * @return array
     */
    function array_sort($arr, $keys, $type = 'desc')
    {
        $key_value = $new_array = array();
        foreach ($arr as $k => $v) {
            $key_value[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($key_value);
        } else {
            arsort($key_value);
        }
        reset($key_value);
        foreach ($key_value as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }
    /**
     * 过滤数组元素前后空格 (支持多维数组)
     * @param $array 要过滤的数组
     * @return array|string
     */
    function trim_array_element($array){
        if(!is_array($array))
            return trim($array);
        return array_map('trim_array_element',$array);
    }
    /**
     * 将二维数组以元素的某个值作为键 并归类数组
     * array( array('name'=>'aa','type'=>'pay'), array('name'=>'cc','type'=>'pay') )
     * array('pay'=>array( array('name'=>'aa','type'=>'pay') , array('name'=>'cc','type'=>'pay') ))
     * @param $arr 数组
     * @param $key 分组值的key
     * @return array
     */
    function group_same_key($arr,$key){
        $new_arr = array();
        foreach($arr as $k=>$v ){
            $new_arr[$v[$key]][] = $v;
        }
        return $new_arr;
    }
    /**
     ** 多个数组的笛卡尔积
     */
    function combineDika() {
        $data = func_get_args();
        $data = current($data);
        $cnt = count($data);
        $result = array();
        $arr1 = array_shift($data);
        foreach($arr1 as $key=>$item)
        {
            $result[] = array($item);
        }
        foreach($data as $key=>$item)
        {
            $result = combineArray($result,$item);
        }
        return $result;
    }
    /**
     **两个数组的笛卡尔积
     */
    function combineArray($arr1,$arr2) {
        $result = array();
        foreach ($arr1 as $item1)
        {
            foreach ($arr2 as $item2)
            {
                $temp = $item1;
                $temp[] = $item2;
                $result[] = $temp;
            }
        }
        return $result;
    }
    /**
     * 	array转xml
     */
    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";

            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }
    //接收Json并转换为数组；
    function getJsonToArray()
    {
        $json = file_get_contents('php://input');
        $array = json_decode($json, true);
        return $array;
    }

    /**
     ** 字符串操作
     */

    //获取随机码
    function getRandCode($length){
        $array = array(
            'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
            '0','1','2','3','4','5','6','8','9'
        );
        $tmpstr ='';
        $max =count($array);
        for($i=1;$i<=$length;$i++){
            $key =rand(0,$max-1);
            $tmpstr.=$array[$key];
        }
        return $tmpstr;
    }
    /**
     **检查手机号码格式
     * @param $mobile 手机号码
     */
    function check_mobile($mobile){
        if(preg_match('/1[34578]\d{9}$/',$mobile))
            return true;
        return false;
    }
    /**
     **检查固定电话
     * @param $mobile
     * @return bool
     */
    function check_telephone($mobile){
        if(preg_match('/^([0-9]{3,4}-)?[0-9]{7,8}$/',$mobile))
            return true;
        return false;
    }
    /**
     ** 检查邮箱地址格式
     * @param $email //邮箱地址
     */
    function check_email($email){
        if(filter_var($email,FILTER_VALIDATE_EMAIL))
            return true;
        return false;
    }
    //截取字符串最后的“。”
    function wxRtrim($arr,$a='。'){
        $arr=rtrim($arr, $a);
        return $arr;
    }
    /**
     *   实现中文字串截取无乱码的方法
     */
    function getSubstr($string, $start, $length) {
        if(mb_strlen($string,'utf-8')>$length){
            $str = mb_substr($string, $start, $length,'utf-8');
            return $str.'...';
        }else{
            return $string;
        }
    }
    /**
     * 替换特殊字符
     * @param unknown 原始字符串
     * @param string 替换字符串
     * @return mixed
     */
    function replaceSpecialStr($orignalStr , $replace=''){
        return preg_replace("/[^\x{4e00}-\x{9fa5}]/iu", $replace ,$orignalStr);
    }
    /**
     **手机号码脱敏
     **/
    function mobile_hide($mobile){
        return substr_replace($mobile,'****',3,4);
    }
    /**
     **URL安全转化
     * @param $string
     * @return mixed|string
     */
    function urlsafe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
    /**
     * 获取整条字符串汉字拼音首字母
     * @param $zh
     * @return string
     */
    function pinyin_long($zh){
        $ret = "";
        $s1 = iconv("UTF-8","gb2312", $zh);
        $s2 = iconv("gb2312","UTF-8", $s1);
        if($s2 == $zh){$zh = $s1;}
        for($i = 0; $i < strlen($zh); $i++){
            $s1 = substr($zh,$i,1);
            $p = ord($s1);
            if($p > 160){
                $s2 = substr($zh,$i++,2);
                $ret .= getFirstCharter($s2);
            }else{
                $ret .= $s1;
            }
        }
        return $ret;
    }
    //php获取中文字符拼音首字母
    function getFirstCharter($str){
        if(empty($str))
        {
            return '';
        }
        $fchar=ord($str{0});
        if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
        $s1=iconv('UTF-8','gb2312//TRANSLIT//IGNORE',$str);
        $s2=iconv('gb2312','UTF-8//TRANSLIT//IGNORE',$s1);
        $s=$s2==$str?$s1:$str;
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319&&$asc<=-20284) return 'A';
        if($asc>=-20283&&$asc<=-19776) return 'B';
        if($asc>=-19775&&$asc<=-19219) return 'C';
        if($asc>=-19218&&$asc<=-18711) return 'D';
        if($asc>=-18710&&$asc<=-18527) return 'E';
        if($asc>=-18526&&$asc<=-18240) return 'F';
        if($asc>=-18239&&$asc<=-17923) return 'G';
        if($asc>=-17922&&$asc<=-17418) return 'H';
        if($asc>=-17417&&$asc<=-16475) return 'J';
        if($asc>=-16474&&$asc<=-16213) return 'K';
        if($asc>=-16212&&$asc<=-15641) return 'L';
        if($asc>=-15640&&$asc<=-15166) return 'M';
        if($asc>=-15165&&$asc<=-14923) return 'N';
        if($asc>=-14922&&$asc<=-14915) return 'O';
        if($asc>=-14914&&$asc<=-14631) return 'P';
        if($asc>=-14630&&$asc<=-14150) return 'Q';
        if($asc>=-14149&&$asc<=-14091) return 'R';
        if($asc>=-14090&&$asc<=-13319) return 'S';
        if($asc>=-13318&&$asc<=-12839) return 'T';
        if($asc>=-12838&&$asc<=-12557) return 'W';
        if($asc>=-12556&&$asc<=-11848) return 'X';
        if($asc>=-11847&&$asc<=-11056) return 'Y';
        if($asc>=-11055&&$asc<=-10247) return 'Z';
        return null;
    }




    // CURL_GET操作
    function httpGet($url){
        $ch=curl_init(); //1.获取初始化URL
        //2.设置curl的参数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        $res = curl_exec($ch);//3.采集
        curl_close($ch);//4.关闭
        if(curl_errno($ch)){
            $res=curl_errno($ch);
        }
        return $res;
    }
    function httpAuthGet($url, $user = 'ylh', $password = '123456')
    {
        $ch = curl_init(); //1.获取初始化URL
        //2.设置curl的参数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$user:$password");
        curl_setopt($ch, CURLOPT_URL, $url);
        $res = curl_exec($ch);//3.采集
        curl_close($ch);//4.关闭
        if (curl_errno($ch)) {
            $res = curl_errno($ch);
        }
        return $res;
    }
    function httpPost($url,$postJson){
        //1.获取初始化URL
        $ch=curl_init();
        //2.设置curl的参数
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);       //设置超时时间
        curl_setopt($ch, CURLOPT_URL, $url);          //设置抓取的url
        curl_setopt($ch, CURLOPT_HEADER, false);        //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_POST, 1);            //设置post方式提交
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postJson);//post变量
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);//3.采集
        curl_close($ch);//4.关闭
        if(curl_errno($ch)){
            $res=curl_errno($ch);
        }
        return $res;
    }
    function httpJsonPost($url, $postJson)
    {
        //1.获取初始化URL
        $ch = curl_init();
        //2.设置curl的参数
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);       //设置超时时间
        curl_setopt($ch, CURLOPT_URL, $url);          //设置抓取的url
        curl_setopt($ch, CURLOPT_HEADER, 0);        //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_POST, 1);            //设置post方式提交
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postJson);//post变量
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);//3.采集
        curl_close($ch);//4.关闭
        if (curl_errno($ch)) {
            $res = curl_errno($ch);
        }
        return $res;
    }
    function httpPut($url, $putJson)
    {
        $ch = curl_init();
        $header[] = "Content-type:image/jpeg";//定义header，可以加多个
        curl_setopt($ch, CURLOPT_URL, $url); //定义请求地址
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "put"); //定义请求类型，当然那个提交类型那一句就不需要了
        curl_setopt($ch, CURLOPT_HEADER, 0); //定义是否显示状态头 1：显示 ； 0：不显示
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//定义header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//定义是否直接输出返回流
        curl_setopt($ch, CURLOPT_POSTFIELDS, $putJson); //定义提交的数据
        $res = curl_exec($ch);
        curl_close($ch);//关闭
        if (curl_errno($ch)) {
            $res = curl_errno($ch);
        }
        return $res;
    }
    function httpAuthPost($url, $postJson, $user = 'ylh', $password = '123456')
    {
        //1.获取初始化URL
        $ch = curl_init();
        //2.设置curl的参数
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);       //设置超时时间
        curl_setopt($ch, CURLOPT_URL, $url);          //设置抓取的url
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$user:$password");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookiefile");
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiefile");
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id);
        curl_setopt($ch, CURLOPT_HEADER, 0);        //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_POST, 1);            //设置post方式提交
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postJson);//post变量
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);//3.采集
        curl_close($ch);//4.关闭
        if (curl_errno($ch)) {
            $res = curl_errno($ch);
        }
        return $res;
    }
    //封装请求API
    function requestApi($url,$method='GET',$data='',$type='array'){
        if($method=='GET'){
            $data = httpGet($url);
        }elseif ($method=='POST'){
            $data = httpJsonPost($url, json_encode($data));
        }elseif ($method=='PUT'){
            $data = httpPut($url, json_encode($data));
        }else{
            return false;
        }
        if($type=='array'){
            $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
        }
        return $data;
    }



    //根据日期获取星期
    function wk($date) {
        $datearr = explode("-",$date);     //将传来的时间使用“-”分割成数组
        $year = $datearr[0];       //获取年份
        $month = sprintf('%02d',$datearr[1]);  //获取月份
        $day = sprintf('%02d',$datearr[2]);      //获取日期
        $hour = $minute = $second = 0;   //默认时分秒均为0
        $dayofweek = mktime($hour,$minute,$second,$month,$day,$year);    //将时间转换成时间戳
        $shuchu = date("w",$dayofweek);      //获取星期值
        $weekarray=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
        return  $weekarray[$shuchu];
    }
    /**
     * 友好时间显示
     * @param $time
     * @return bool|string
     */
    function friend_date($time)
    {
        if (!$time)
            return false;
        $fdate = '';
        $d = time() - intval($time);
        $ld = $time - mktime(0, 0, 0, 0, 0, date('Y')); //得出年
        $md = $time - mktime(0, 0, 0, date('m'), 0, date('Y')); //得出月
        $byd = $time - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')); //前天
        $yd = $time - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
        $dd = $time - mktime(0, 0, 0, date('m'), date('d'), date('Y')); //今天
        $td = $time - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')); //明天
        $atd = $time - mktime(0, 0, 0, date('m'), date('d') + 2, date('Y')); //后天
        if ($d == 0) {
            $fdate = '刚刚';
        } else {
            switch ($d) {
                case $d < $atd:
                    $fdate = date('Y年m月d日', $time);
                    break;
                case $d < $td:
                    $fdate = '后天' . date('H:i', $time);
                    break;
                case $d < 0:
                    $fdate = '明天' . date('H:i', $time);
                    break;
                case $d < 60:
                    $fdate = $d . '秒前';
                    break;
                case $d < 3600:
                    $fdate = floor($d / 60) . '分钟前';
                    break;
                case $d < $dd:
                    $fdate = floor($d / 3600) . '小时前';
                    break;
                case $d < $yd:
                    $fdate = '昨天' . date('H:i', $time);
                    break;
                case $d < $byd:
                    $fdate = '前天' . date('H:i', $time);
                    break;
                case $d < $md:
                    $fdate = date('m月d日 H:i', $time);
                    break;
                case $d < $ld:
                    $fdate = date('m月d日', $time);
                    break;
                default:
                    $fdate = date('Y年m月d日', $time);
                    break;
            }
        }
        return $fdate;
    }



    //获取登录用户
    function getLoginUser(){
        $user=jie_mi(cookie(C(PRODUCT).'_user'));
        return $user;
    }
    function getLoginUserID(){
        $userID=jie_mi(cookie(C(PRODUCT).'_user_id'));
        return $userID;
    }


    /**
     ** Cookie相关的操作
     * @param $key
     * @return mixed
     */
    //获取cookiekey的信息
    function getCookieKey($key){
        $data=cookie(C(PRODUCT).'_'.$key);
        return $data;
    }
    //设置cookiekey
    function setCookieKey($key,$value,$expire=3600){
        cookie($key,$value,array('expire'=>$expire,'prefix'=>C(PRODUCT).'_'));
    }
    //清除cookie
    function clearCookie(){
        //  清空指定前缀的所有cookie值
        $res=cookie(null,C(PRODUCT).'_');
        return $res;
    }

    //获取某一字典值
    function getDictValue($type,$key,$value='value'){
        $where=array('type'=>$type,'key'=>$key,'deleted'=>'0');
        $data=M('tp_dict')->where($where)->find();
        if($data[$value]){
            return $data[$value];
        }else{
            return $key;
        }
    }
    //获取列表
    function get_list($table,$where,$name='name',$order='id'){
        $data=M($table)->where($where)->order($order)->select();
        $list=array();
        foreach ($data as $k=>$da){
            $list[$k]['key']=$da['id'];
            $list[$k]['value']=$da[$name];
        }
        return $list;
    }
    //获取字典列表
    function get_dict_list($type,$table='tp_dict',$lim=''){
        $where=array('type'=>$type,'deleted'=>'0');
        if($lim){
            $where['key']=array('in',$lim);
        }
        $data=M($table)->where($where)->order('sn')->select();
        return $data;
    }
    //获取字典信息
    function get_dict_info($type,$key,$table='tp_dict',$what='value'){
        $where=array('type'=>$type,'key'=>$key,'deleted'=>'0');
        $data=M($table)->where($where)->find();
        if($what=='value'){
            return $data['value'];
        }else{
            return $data;
        }
    }
    //封装字典为下拉菜单
    function dict_list($type,$field,$table='tp_dict',$default='0',$lim=''){
        $data=get_dict_list($type,$table,$lim);
        $var=select($data,$field,$default);
        return $var;
    }
    //基础下拉菜单
    function select($data, $name, $value)
    {
        $html = '<select name="' . $name . '" class="form-control">';
            foreach ($data as $v) {
                $selected = ($v['key'] == $value) ? "selected" : "";
                $html .= '<option ' . $selected . ' value="' . $v['key'] . '">' . $v['value'] . '</option>';
            }
        $html .= '</select>';
        return $html;
    }


    //获取加密签名
    function get_sign($array,$_timestamp,$appkey,$type='md5'){
        //判定$array是否为数组
        if(!is_array($array)){
            $array=json_decode($array,TRUE);
        }
        $array['_timestamp']=date('Y-m-d H:i:s',$_timestamp);
        foreach ($array as $key=>$value){
            $arr[$key] = $key;
        }
        sort($arr);//将数组按
        //拼接字符串
        $str = $appkey;
        foreach ($arr as $k => $v) {
            $str = $str.$arr[$k].$array[$v];
        }
        if($type=='md5'){
            //数据加密并转换为大写
            $str=strtoupper(md5($str));
        }elseif ($type=='sha1'){
            //数据加密并转换为大写
            $str=strtoupper(sha1($str));
        }else{
            $str='加密方式暂不支持';
        }
        return $str;
    }
    //校验签名
    function verify_sign($array,$appkey,$type='md5')
    {
        $clientSign=$array['_sign'];
        unset($array['_sign']);
        foreach ($array as $key=>$value){
            $arr[$key] = $key;
        }
        sort($arr);//将数组按
        $serverstr = "";
        foreach ($arr as $k => $v) {
            $serverstr = $serverstr.$arr[$k].$array[$v];
        }
        #生成服务端str
        $reserverstr=$appkey.$serverstr;
        if($type=='md5'){
            $reserverSign = strtoupper(md5($reserverstr));
        }elseif ($type=='sha1'){
            $reserverSign = strtoupper(sha1($reserverstr));
        }
        if($clientSign!=$reserverSign){
            return 0;//验证失败
        }else{
            return 1;//验证成功
        }
    }

    function get_token(){
        if (!S(C(PRODUCT) . 'access_token')) {//缓存中没有token先获取token，并设置失效时间
            $url=C(WEBSERVER).'/index.php/Api/Oauth/token'.'?grant_type=client_credential'.'&appid='.C(XL_APPID).'&secret='.C(XL_SECRET);
            $data=httpGet($url);
            $info = json_decode(trim($data,chr(239).chr(187).chr(191)),true);
            S(C(PRODUCT) . 'access_token', $info['data']['access_token'], 7200);
            return S(C(PRODUCT) . 'access_token');
        } else {//缓存中有token直接返回
            return S(C(PRODUCT) . 'access_token');
        }
    }
    function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 4;
        // 密匙
        $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);
        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length):
            substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
        //解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
            sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
                substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }

    //Excel相关的函数
    /**
     **根据下标获得单元格所在列位置
     **/
    function getCells($index){
        $arr=array(
            'A','B','C','D','E','F','G','H','I','J','K','L','M',
            'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM',
            'AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'
        );
        return $arr[$index];
    }
    /**
     **获取边框样式代码
     **/
    function getBorderStyle($color){
        $styleArray = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THICK,
                    'color' => array('rgb' => $color),
                ),
            ),
        );
        return $styleArray;
    }
    /**
     ** 导出Excel到浏览器
     * @param $type
     * @param $filename
     * @param $objPHPExcel
     */
    function browser_export($type,$filename,$objPHPExcel){
        //清除缓冲区,避免乱码
        ob_end_clean();
        if($type=='PDF'){
            header('Content-Type: application/pdf');
        }elseif ($type=='Excel5'){
            header('Content-Type: application/vnd.ms-excel');
        }elseif ($type=='OpenDocument'){
            header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
        }else{
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $objWriter=\PHPExcel_IOFactory::createWriter($objPHPExcel,$type);
        $objWriter->save('php://output');
    }

    /**
     **微信公众号相关
     */
    function getAccessToken($appID,$appsecret,$type='') {
        $access_token= S($appID.'access_token');
        if (!$access_token) {
            if($type){
                // 如果是企业号用以下URL获取access_token
                $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$appID&corpsecret=$appsecret";
            }else{
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appID."&secret=".$appsecret;
            }
            $res = json_decode(httpGet($url));
            $access_token = $res->access_token;
            if($access_token){
                S($appID.'access_token',$access_token,7000);
            }
        }
        return $access_token;
    }


    /**
     * 自定义函数递归的复制带有多级子目录的目录
     * 递归复制文件夹
     * @param type $src 原目录
     * @param type $dst 复制到的目录
     */
    //参数说明：
    //自定义函数递归的复制带有多级子目录的目录
    function recurse_copy($src, $dst)
    {
        $now = time();
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== $file = readdir($dir)) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    recurse_copy($src . '/' . $file, $dst . '/' . $file);
                }
                else {
                    if (file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
                        if (!is_writeable($dst . DIRECTORY_SEPARATOR . $file)) {
                            exit($dst . DIRECTORY_SEPARATOR . $file . '不可写');
                        }
                        @unlink($dst . DIRECTORY_SEPARATOR . $file);
                    }
                    if (file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
                        @unlink($dst . DIRECTORY_SEPARATOR . $file);
                    }
                    $copyrt = copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                    if (!$copyrt) {
                        echo 'copy ' . $dst . DIRECTORY_SEPARATOR . $file . ' failed<br>';
                    }
                }
            }
        }
        closedir($dir);
    }

    // 递归删除文件夹
    function delFile($path,$delDir = FALSE) {
        if(!is_dir($path))
            return FALSE;
        $handle = @opendir($path);
        if ($handle) {
            while (false !== ( $item = readdir($handle) )) {
                if ($item != "." && $item != "..")
                    is_dir("$path/$item") ? delFile("$path/$item", $delDir) : unlink("$path/$item");
            }
            closedir($handle);
            if ($delDir) return rmdir($path);
        }else {
            if (file_exists($path)) {
                return unlink($path);
            } else {
                return FALSE;
            }
        }
    }


    /**
     * 比较两个版本大小, $v1>v2:1 ; $v1=v2:0 ;$v1<v2:0
     * @param unknown $v1
     * @param unknown $v2
     * @return number
     */
    function compareVersion($v1, $v2) {
        $v1 = explode(".",$v1);
        $v2 =  explode(".",$v2);
        $len = max(count($v1), count($v2));

        while(count($v1) < $len) {
            array_push($v1, 0);
        }

        while(count($v2) < $len) {
            array_push($v2, 0);
        }
        for($i = 0; $i < $len;$i++) {
            $num1 = intval($v1[$i]);
            $num2 = intval($v2[$i]);
            if ($num1 > $num2) {
                return 1;
            } else if ($num1 < $num2) {
                return -1;
            }
        }
        return 0;
    }


    /**
     * 创建bmp格式图片
     *
     * @author: legend(legendsky@hotmail.com)
     * @link: http://www.ugia.cn/?p=96
     * @description: create Bitmap-File with GD library
     * @version: 0.1
     *
     * @param resource $im          图像资源
     * @param string   $filename    如果要另存为文件，请指定文件名，为空则直接在浏览器输出
     * @param integer  $bit         图像质量(1、4、8、16、24、32位)
     * @param integer  $compression 压缩方式，0为不压缩，1使用RLE8压缩算法进行压缩
     *
     * @return integer
     */
    function imagebmp(&$im, $filename = '', $bit = 8, $compression = 0)
    {
        if (!in_array($bit, array(1, 4, 8, 16, 24, 32)))
        {
            $bit = 8;
        }
        else if ($bit == 32) // todo:32 bit
        {
            $bit = 24;
        }
        $bits = pow(2, $bit);
        // 调整调色板
        imagetruecolortopalette($im, true, $bits);
        $width  = imagesx($im);
        $height = imagesy($im);
        $colors_num = imagecolorstotal($im);
        if ($bit <= 8)
        {
            // 颜色索引
            $rgb_quad = '';
            for ($i = 0; $i < $colors_num; $i ++)
            {
                $colors = imagecolorsforindex($im, $i);
                $rgb_quad .= chr($colors['blue']) . chr($colors['green']) . chr($colors['red']) . "\0";
            }
            // 位图数据
            $bmp_data = '';
            // 非压缩
            if ($compression == 0 || $bit < 8)
            {
                if (!in_array($bit, array(1, 4, 8)))
                {
                    $bit = 8;
                }
                $compression = 0;
                // 每行字节数必须为4的倍数，补齐。
                $extra = '';
                $padding = 4 - ceil($width / (8 / $bit)) % 4;
                if ($padding % 4 != 0)
                {
                    $extra = str_repeat("\0", $padding);
                }
                for ($j = $height - 1; $j >= 0; $j --)
                {
                    $i = 0;
                    while ($i < $width)
                    {
                        $bin = 0;
                        $limit = $width - $i < 8 / $bit ? (8 / $bit - $width + $i) * $bit : 0;
                        for ($k = 8 - $bit; $k >= $limit; $k -= $bit)
                        {
                            $index = imagecolorat($im, $i, $j);
                            $bin |= $index << $k;
                            $i ++;
                        }
                        $bmp_data .= chr($bin);
                    }
                    $bmp_data .= $extra;
                }
            }
            // RLE8 压缩
            else if ($compression == 1 && $bit == 8)
            {
                for ($j = $height - 1; $j >= 0; $j --)
                {
                    $last_index = "\0";
                    $same_num   = 0;
                    for ($i = 0; $i <= $width; $i ++)
                    {
                        $index = imagecolorat($im, $i, $j);
                        if ($index !== $last_index || $same_num > 255)
                        {
                            if ($same_num != 0)
                            {
                                $bmp_data .= chr($same_num) . chr($last_index);
                            }
                            $last_index = $index;
                            $same_num = 1;
                        }
                        else
                        {
                            $same_num ++;
                        }
                    }
                    $bmp_data .= "\0\0";
                }
                $bmp_data .= "\0\1";
            }
            $size_quad = strlen($rgb_quad);
            $size_data = strlen($bmp_data);
        }
        else
        {
            // 每行字节数必须为4的倍数，补齐。
            $extra = '';
            $padding = 4 - ($width * ($bit / 8)) % 4;
            if ($padding % 4 != 0)
            {
                $extra = str_repeat("\0", $padding);
            }
            // 位图数据
            $bmp_data = '';
            for ($j = $height - 1; $j >= 0; $j --)
            {
                for ($i = 0; $i < $width; $i ++)
                {
                    $index  = imagecolorat($im, $i, $j);
                    $colors = imagecolorsforindex($im, $index);
                    if ($bit == 16)
                    {
                        $bin = 0 << $bit;
                        $bin |= ($colors['red'] >> 3) << 10;
                        $bin |= ($colors['green'] >> 3) << 5;
                        $bin |= $colors['blue'] >> 3;
                        $bmp_data .= pack("v", $bin);
                    }
                    else
                    {
                        $bmp_data .= pack("c*", $colors['blue'], $colors['green'], $colors['red']);
                    }
                    // todo: 32bit;
                }
                $bmp_data .= $extra;
            }
            $size_quad = 0;
            $size_data = strlen($bmp_data);
            $colors_num = 0;
        }
        // 位图文件头
        $file_header = "BM" . pack("V3", 54 + $size_quad + $size_data, 0, 54 + $size_quad);
        // 位图信息头
        $info_header = pack("V3v2V*", 0x28, $width, $height, 1, $bit, $compression, $size_data, 0, 0, $colors_num, 0);
        // 写入文件
        if ($filename != '')
        {
            $fp = fopen("test.bmp", "wb");
            fwrite($fp, $file_header);
            fwrite($fp, $info_header);
            fwrite($fp, $rgb_quad);
            fwrite($fp, $bmp_data);
            fclose($fp);
            return 1;
        }
        // 浏览器输出
        header("Content-Type: image/bmp");
        echo $file_header . $info_header;
        echo $rgb_quad;
        echo $bmp_data;
        return 1;
    }
    /**
     * BMP 创建函数
     * @author simon
     * @param string $filename path of bmp file
     * @example who use,who knows
     * @return resource of GD
     */
    function imagecreatefrombmp( $filename ){
        if ( !$f1 = fopen( $filename, "rb" ) )
            return FALSE;
        $FILE = unpack( "vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread( $f1, 14 ) );
        if ( $FILE['file_type'] != 19778 )
            return FALSE;
        $BMP = unpack( 'Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' . '/Vcompression/Vsize_bitmap/Vhoriz_resolution' . '/Vvert_resolution/Vcolors_used/Vcolors_important', fread( $f1, 40 ) );
        $BMP['colors'] = pow( 2, $BMP['bits_per_pixel'] );
        if ( $BMP['size_bitmap'] == 0 )
            $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
        $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
        $BMP['bytes_per_pixel2'] = ceil( $BMP['bytes_per_pixel'] );
        $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
        $BMP['decal'] -= floor( $BMP['width'] * $BMP['bytes_per_pixel'] / 4 );
        $BMP['decal'] = 4 - (4 * $BMP['decal']);
        if ( $BMP['decal'] == 4 )
            $BMP['decal'] = 0;
        $PALETTE = array();
        if ( $BMP['colors'] < 16777216 ){
            $PALETTE = unpack( 'V' . $BMP['colors'], fread( $f1, $BMP['colors'] * 4 ) );
        }
        $IMG = fread( $f1, $BMP['size_bitmap'] );
        $VIDE = chr( 0 );
        $res = imagecreatetruecolor( $BMP['width'], $BMP['height'] );
        $P = 0;
        $Y = $BMP['height'] - 1;
        while( $Y >= 0 ){
            $X = 0;
            while( $X < $BMP['width'] ){
                if ( $BMP['bits_per_pixel'] == 32 ){
                    $COLOR = unpack( "V", substr( $IMG, $P, 3 ) );
                    $B = ord(substr($IMG, $P,1));
                    $G = ord(substr($IMG, $P+1,1));
                    $R = ord(substr($IMG, $P+2,1));
                    $color = imagecolorexact( $res, $R, $G, $B );
                    if ( $color == -1 )
                        $color = imagecolorallocate( $res, $R, $G, $B );
                    $COLOR[0] = $R*256*256+$G*256+$B;
                    $COLOR[1] = $color;
                }elseif ( $BMP['bits_per_pixel'] == 24 )
                    $COLOR = unpack( "V", substr( $IMG, $P, 3 ) . $VIDE );
                elseif ( $BMP['bits_per_pixel'] == 16 ){
                    $COLOR = unpack( "n", substr( $IMG, $P, 2 ) );
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }elseif ( $BMP['bits_per_pixel'] == 8 ){
                    $COLOR = unpack( "n", $VIDE . substr( $IMG, $P, 1 ) );
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }elseif ( $BMP['bits_per_pixel'] == 4 ){
                    $COLOR = unpack( "n", $VIDE . substr( $IMG, floor( $P ), 1 ) );
                    if ( ($P * 2) % 2 == 0 )
                        $COLOR[1] = ($COLOR[1] >> 4);
                    else
                        $COLOR[1] = ($COLOR[1] & 0x0F);
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }elseif ( $BMP['bits_per_pixel'] == 1 ){
                    $COLOR = unpack( "n", $VIDE . substr( $IMG, floor( $P ), 1 ) );
                    if ( ($P * 8) % 8 == 0 )
                        $COLOR[1] = $COLOR[1] >> 7;
                    elseif ( ($P * 8) % 8 == 1 )
                        $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                    elseif ( ($P * 8) % 8 == 2 )
                        $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                    elseif ( ($P * 8) % 8 == 3 )
                        $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                    elseif ( ($P * 8) % 8 == 4 )
                        $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                    elseif ( ($P * 8) % 8 == 5 )
                        $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                    elseif ( ($P * 8) % 8 == 6 )
                        $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                    elseif ( ($P * 8) % 8 == 7 )
                        $COLOR[1] = ($COLOR[1] & 0x1);
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }else
                    return FALSE;
                imagesetpixel( $res, $X, $Y, $COLOR[1] );
                $X++;
                $P += $BMP['bytes_per_pixel'];
            }
            $Y--;
            $P += $BMP['decal'];
        }
        fclose( $f1 );
        return $res;
    }
