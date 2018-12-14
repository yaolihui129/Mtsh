<?php
namespace Jira\Controller;
use PHPExcel_IOFactory;
use PHPExcel;
use Behavior;
class ExcelController extends WebInfoController
{
    public function index()
    {
        $this->display();
    }

    function daochu(){
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel= new PHPExcel();
        $objSheet=$objPHPExcel->getActiveSheet();
        $objSheet->setTitle('Demo');
        $array=M('pro_info')->select();
        //     $objSheet->fromArray($array);//直接加载数组，内存消耗较大不建议使用
        //     $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);//所有单元格（行）默认高度
        //     $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);//所有单元格（列）默认宽度
        //     $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);//设置行高度
        //     $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);//设置列宽度
        //     $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);// 设置文字颜色
        //     $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中

        $objSheet   ->setCellValue("A1","序号")
                    ->setCellValue("B1","商品名")
                    ->setCellValue("C1","价格")
                    ->setCellValue("D1","数量");
        $objSheet   ->getStyle('A1:D1')->getFill()
                    ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('6fc144');//填充班级背景颜色
        $objSheet   ->getDefaultStyle()->getFont()
                    ->setSize(14)->setName("微软雅黑");//设置默认字体大小和格式
        $objSheet   ->getStyle('A1:D1')->getFont()
                    ->setSize(13)->setBold(true);//设置字体大小和加粗

        foreach ($array as $key =>$ar){
            $j=2;
            $objSheet   ->setCellValue("A".($key+$j),$ar['pid'])
                        ->setCellValue("B".($key+$j),$ar['pname'])
                        ->setCellValue("C".($key+$j),$ar['pprice'])
                        ->setCellValue("D".($key+$j),$ar['pcount']);
        }
        //设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
        $objSheet->getStyle('A1:D55')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $type='Excel2007';//Excel5,Excel2007,PDF,OpenDocument
        $filename='demo.xlsx';//xls,xlsx,pdf,obs
        browser_export($type,$filename,$objPHPExcel);
    }



    public function upload() {
        ini_set('memory_limit','1024M');
        if (!empty($_FILES)) {
            $config = array(
                'exts' => array('xlsx','xls'),
                'maxSize' => 3145728000,
                'rootPath' =>"./Upload",
                'savePath' => '/Excel/',
                'subName' => array('date','Ymd'),
            );
            $upload = new \Think\Upload($config);
            if (!$info = $upload->upload()) {
                $this->error($upload->getError());
            }
            vendor("PHPExcel.PHPExcel");
            $file_name=$upload->rootPath.$info['photo']['savepath'].$info['photo']['savename'];
            $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));//判断导入表格后缀格式
            if ($extension == 'xlsx') {
                $objReader =\PHPExcel_IOFactory::createReader('Excel2007');
            } else if ($extension == 'xls'){
                $objReader =\PHPExcel_IOFactory::createReader('Excel5');
            }else if($extension == 'cvs'){
                $objReader =\PHPExcel_IOFactory::createReader('CVS');
            }
            $objPHPExcel =$objReader->load($file_name, $encode = 'utf-8');
            $sheet =$objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();//取得总行数
            $highestColumn =$sheet->getHighestColumn(); //取得总列数
            D('pro_info')->execute('truncate table pro_info');//删除表中所有数据
            for ($i = 2; $i <= $highestRow; $i++) {
                //看这里看这里,前面小写的a是表中的字段名，后面的大写A是excel中位置
                $data['pId'] =$objPHPExcel->getActiveSheet()->getCell("A" . $i)->getValue();
                $data['pName'] =$objPHPExcel->getActiveSheet()->getCell("B" .$i)->getValue();
                $data['pPrice'] =$objPHPExcel->getActiveSheet()->getCell("C" .$i)->getValue();
                $data['pCount'] = $objPHPExcel->getActiveSheet()->getCell("D". $i)->getValue();
                //看这里看这里,这个位置写数据库中的表名
                D('pro_info')->add($data);
            }
            $this->success('导入成功!');
        }else {
            $this->error("请选择上传的文件");
        }
    }




}