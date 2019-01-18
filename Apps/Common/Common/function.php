<?php
use Think\Model;
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
    function realDel($table,$arrId)
    {
        $count = D($table)->delete($arrId);
        return $count;
    }
    function getList($table,$where,$order = 'id', $field = '',$page='',$size=''){
        $where['deleted'] = '0';
        $where['removed'] = '0';
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
        $where['removed'] = '0';
        $data = M($table)->where($where)->order($order)->field($field)->find();
        return $data;
    }
    function countId($table,$where){
        $where['deleted'] = '0';
        $where['removed'] = '0';
        $count=M($table)->where($where)->count();
        return $count;
    }
    function countWithParent($table,$field,$parentId){
        $where=array($field=>$parentId);
        $count=countId($table,$where);
        return $count;
    }
    function sum($table,$where,$field){
        $where['deleted'] = '0';
        $where['removed'] = '0';
        $sum=M($table)->where($where)->sum($field);
        if ($sum){
            return $sum;
        }else{
            return 0;
        }
    }
    function filterID($table,$map){
        $var=getList($table,$map);
        $id=array();
        foreach ($var as $v){
            $id[]=$v['id'];
        }
        return $id;
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
     * @param $assign
     * @param $table
     * @param $where
     * @param string $order
     * @param string $field
     * @param string $page
     * @param string $size
     * @return bool
     */
    function baseGetList($assign,$table,$where,$order = 'id', $field = '',$page='',$size=''){
        $data=getList($table,$where,$order, $field,$page,$size);
        $this->assign($assign, $data);
        return true;
    }

    /**
     **对象输出
     * @param $init_table
     * @param $data
     * @return mixed
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
        $res = resFormat($data);
        $this->ajaxReturn($res);
    }

    /**
     **格式化输出
     */
    function resFormat($data,$code='0',$message='ok'){
        if($data){
            $res=array(
                'errorcode'=>$code,
                'message'=>$message,
                'result'=>$data
            );
        }else{
            $res=array(
                'errorcode'=>$code,
                'message'=>$message
            );
        }
        return $res;
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
            $key=C('PRODUCT');
        }
        $data=lock_url($data,$key,$type);
        return $data;
    }
    function jie_mi($data,$key=''){
        if(!$key){
            $key=C('PRODUCT');
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
        $k = 0;
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
        $k = 0;
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
    function getParentModule($id){
        $data=M("module")->find($id);
        if($data['parent']){
            $str=getModuleName($data['parent']).'-';
        }else{
            $str='';
        }
        return $str;
    }
    function getModuleName($pathid){
        $data=M('module')->find($pathid);
        if ($data['parent']){
            $str=getModuleName($data['parent']).'-'.$data['name'];
        }else {
            $str=$data['name'];
        }
        return $str;
    }
    function getCatName($cateid){
        if ($cateid){
            $data=M('tp_cate')->find($cateid);
            $str=getCatname($data['pidcateid'])."-".$data['catname'];
            return $str;
        }else {
            return "|-";
        }
    }
    function getCatePid($cateId){
        $data=M('tp_cate')->find($cateId);
        return $data['pidcateid'];
    }



    function getWebsite(){
        if(isHttps()){
            $website='https://'.$_SERVER['SERVER_NAME'];
        }else{
            $website='http://'.$_SERVER['SERVER_NAME'];
        }
        if(!C('ONLINE')){
            $website=$website.'/Demo';
        }
        return $website;
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
        $where=array('phone'=>$phone,'password'=>md5($password));
        $data=M('tp_credit')->where($where)->find();
        if ($data){
            $_SESSION['isCLogin']= C('PRODUCT');
            $_SESSION['realname']= $data['realname'];
            $m=M('tp_customer');
            $where=array('creditid'=>$data['creditid'],'prodid'=>C('PRODID'));
            $arr=$m->where($where)->find();
            if($arr){
            $_SESSION['userid'] =   $arr['id'];
            $_POST['id']        =   $arr['id'];
            $_POST['lastLoginTime']=date("Y-m-d H:i:s",time());
            $_POST['lastLoginIP']=get_client_ip();
            $m->save($_POST);//更新最后登录信息
            }else {
            $_POST['prodid']=C('PRODID');
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
    function isWeiXin() {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        } return false;
    }
    //是否为QQ
    function isQQ() {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'QQ') !== false) {
            return true;
        } return false;
    }
    //是否为支付宝
    function isAliPay() {
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
        }else{
            $ip = '';
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
    function isHttps()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off';
    }

    /**
     *数组相关操作
    /**
     * 二维数组排序
     * @param $arr
     * @param $keys
     * @param string $type
     * @return array
     */
    function arraySort($arr, $keys, $type = 'desc')
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
     */
    function trimArrayElement($arr){
        if(!is_array($arr))
            return trim($arr);
        return array_map('trim_array_element',$arr);
    }
    /**
     * 将二维数组以元素的某个值作为键 并归类数组
     * array( array('name'=>'aa','type'=>'pay'), array('name'=>'cc','type'=>'pay') )
     * array('pay'=>array( array('name'=>'aa','type'=>'pay') , array('name'=>'cc','type'=>'pay') ))
     */
    function groupSameKey($arr,$key){
        $new_arr = array();
        foreach($arr as $k=>$v ){
            $new_arr[$v[$key]][] = $v;
        }
        return $new_arr;
    }

    /**
     * 计算多个集合的笛卡尔积
     */
    function CartesianProduct($sets){
        $result = array();
        // 循环遍历集合数据
        for($i=0,$count=count($sets); $i<$count-1; $i++){
            // 初始化
            if($i==0){
                $result = $sets[$i];
            }
            $tmp = array();
            // 结果与下一个集合计算笛卡尔积
            foreach($result as $res){
                foreach($sets[$i+1] as $set){
                    $tmp[] = $res.$set;
                }
            }
            // 将笛卡尔积写入结果
            $result = $tmp;
        }
        return $result;
    }
    /**
     * 	array转xml
     */
    function arrayToXml($arr){
        $xml = "";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        return  "<xml>".$xml."</xml>";
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
            '0','1','2','3','4','5','6','8','9','_'
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
     *检查手机号码格式
     */
    function checkMobile($mobile){
        if(preg_match('/1[34578]\d{9}$/',$mobile))
            return true;
        return false;
    }
    /**
     *检查固定电话
     */
    function checkTelephone($mobile){
        if(preg_match('/^([0-9]{3,4}-)?[0-9]{7,8}$/',$mobile))
            return true;
        return false;
    }
    /**
     * 检查邮箱地址格式
     */
    function checkEmail($email){
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
    function getSubStr($string, $start, $length) {
        if(mb_strlen($string,'utf-8')>$length){
            $str = mb_substr($string, $start, $length,'utf-8');
            return $str.'...';
        }else{
            return $string;
        }
    }
    /**
     * 替换特殊字符
     */
    function replaceSpecialStr($orignalStr , $replace=''){
        return preg_replace("/[^\x{4e00}-\x{9fa5}]/iu", $replace ,$orignalStr);
    }
    /**
     **手机号码脱敏
     **/
    function mobileHide($mobile){
        return substr_replace($mobile,'****',3,4);
    }
    /**
     *URL安全转化
     */
    function urlSafeB4encode($uri)
    {
        $data = base64_encode($uri);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
    /**
     * 获取整条字符串汉字拼音首字母
     */
    function pinYinLong($zh){
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
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        $res = curl_exec($ch);
        curl_close($ch);
        if(curl_errno($ch)){
            $res=curl_errno($ch);
        }
        return $res;
    }
    function httpAuthGet($url, $user, $password)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$user:$password");
        curl_setopt($ch, CURLOPT_URL, $url);
        $res = curl_exec($ch);
        curl_close($ch);
        if (curl_errno($ch)) {
            $res = curl_errno($ch);
        }
        return $res;
    }
    function httpPost($url,$postJson){
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postJson);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);
        if(curl_errno($ch)){
            $res=curl_errno($ch);
        }
        return $res;
    }
    function httpJsonPost($url, $postJson)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);
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
    function httpAuthPost($url, $postJson,$user,$password)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$user:$password");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookiefile");
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiefile");
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);
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
    function getWeek($date) {
        $dateArr = explode("-",$date);     //将传来的时间使用“-”分割成数组
        $year = $dateArr[0];       //获取年份
        $month = sprintf('%02d',$dateArr[1]);  //获取月份
        $day = sprintf('%02d',$dateArr[2]);      //获取日期
        $hour = $minute = $second = 0;   //默认时分秒均为0
        $dayOfWeek = mktime($hour,$minute,$second,$month,$day,$year);    //将时间转换成时间戳
        $shuChu = date("w",$dayOfWeek);      //获取星期值
        $weekArray=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
        return  $weekArray[$shuChu];
    }
    /**
     * 友好时间显示
     */
    function friendDate($time)
    {
        if (!$time)
            return false;
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




    /**
     ** Cookie相关的操作
     */
    function getCookieKey($key){
        $data=cookie(C('PRODUCT').'_'.$key);
        return $data;
    }
    function setCookieKey($key,$value,$expire=3600){
        cookie($key,$value,array('expire'=>$expire,'prefix'=>C('PRODUCT').'_'));
    }
    function clearCookie(){
        //  清空指定前缀的所有cookie值
        $res=cookie(null,C('PRODUCT').'_');
        return $res;
    }
    /**
     ** Session相关的操作
     */
    function getSession($key){
        return $_SESSION[C('PRODUCT')][$key];
    }
    function setSession($key,$value){
        $_SESSION[C('PRODUCT')][$key]=$value;
    }
    function clearSession(){
        $_SESSION[C('PRODUCT')]='';
    }
    function getCache($key){
        $value=getSession($key);
        if(!$value){
            $value=getCookieKey($key);
            if($value){
                setSession($key,$value);
            }
        }
        return $value;
    }
    function setCache($key,$value,$expire=7*24*3600){
        setSession($key,$value);
        setCookieKey($key,$value,$expire);
        return true;
    }

    //获取登录用户
    function getLoginUser(){
        $user = getCache('user');
        $user = jie_mi($user);
        return $user;
    }
    function getLoginUserID(){
        $userID = getCache('user_id');
        $userID =jie_mi($userID);
        return $userID;
    }


    //获取某一字典值
    function getDictValue($type,$key,$value='value',$table='tp_dict'){
        $where=array('type'=>$type,'key'=>$key);
        $data = findOne($table,$where);
        if($data[$value]){
            return $data[$value];
        }else{
            return $key;
        }
    }
    //获取列表
    function getKVList($table,$where,$name='name',$order='id'){
        $data = getList($table,$where,$order);
        $list=array();
        foreach ($data as $k=>$da){
            $list[$k]['key']=$da['id'];
            $list[$k]['value']=$da[$name];
        }
        return $list;
    }
    //获取字典列表
    function getDictList($type,$table='tp_dict',$lim=''){
        $where=array('type'=>$type);
        if($lim){
            $where['key']=array('in',$lim);
        }
        return getList($table,$where,'sn');
    }
    //获取字典信息
    function getDictInfo($type,$key,$table='tp_dict',$what='value'){
        $where=array('type'=>$type,'key'=>$key);
        $data = findOne($table,$where);
        return $data[$what];
    }
    //封装字典为下拉菜单
    function dictList($type,$field,$table='tp_dict',$default='0',$lim=''){
        $data=getDictList($type,$table,$lim);
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
    function getSign($array,$_timestamp,$appkey,$type='md5'){
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
    function verifySign($array,$appkey,$type='md5')
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

    function getToken(){
        $product=C('PRODUCT');
        if (!S($product. 'access_token')) {//缓存中没有token先获取token，并设置失效时间
            $url=C('WEBSERVER').'/Api/Oauth/token'.'?grant_type=client_credential'.'&appid='.C('XL_APPID').'&secret='.C('XL_SECRET');
            $data=httpGet($url);
            $info = json_decode(trim($data,chr(239).chr(187).chr(191)),true);
            S($product . 'access_token', $info['data']['access_token'], 7200);
            return S($product . 'access_token');
        } else {//缓存中有token直接返回
            return S($product . 'access_token');
        }
    }
    function authCode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
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
     *根据下标获得单元格所在列位置
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
     **导出Excel到浏览器
     *  $type
     * $filename
     * $objPHPExcel
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
     * 比较两个版本大小, $v1>v2:1 ; $v1=v2:0 ;$v1<v2:0
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


