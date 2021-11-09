<?php
namespace app\questionnaireadmin\controller;
use think\Controller;
use think\Db;

class Admin extends Common{

	public function questionnaire($id = 0, $tab = 3, $category = 0){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('questionnaire')->where(['id'=>$key,'category'=>input('post.category')])->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('questionnaire').'?category='.input('post.category'));
		}else{
			
			$info = Db::name('questionnaire')->where(['category'=>$category])->order('sort asc')->paginate(10,false,['query'=>request()->param()]);

			$page = $info->render();

			$this->assign('info',$info);
			$this->assign('page',$page);
			$this->assign('category',$category);

			// return $this->fetch();

			if($tab == 3){
				$infoEdit = Db::name('questionnaire')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}		
		return $this->fetch();
	}

	/*
	* PETMR - 新增数据
	*/
	public function addQuestionnaire(){
		if(request()->isPost()){
			$data = input('post.');
			$data['ip'] = request()->ip();
			$data['create_time'] = date('Y-m-d H:i:s',time());		

			$info = Db::name('questionnaire')->where(['category'=>input('post.category')])->insert($data);
			if($info){
				return success('添加成功',url('questionnaire').'?category='.input('post.category'));
			}else{
				return error('添加失败');
			}
		}else{
			echo 111111;
		}
	}


	/*
	* PETMR - 修改数据
	*/
	public function editQuestionnaire(){
		if(request()->isPost()){
			$data = input('post.');
			$data['update_time'] = date('Y-m-d H:i:s',time());
			$id = input('post.id');
			$info = Db::name('questionnaire')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('questionnaire').'?category='.input('post.category'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* PETMR - 删除数据
	*/
	public function deleteQuestionnaire($id = 0, $category = 0){

		$info = Db::name('questionnaire')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('questionnaire').'?category='.$category);
		}else{
			return error('删除失败');
		}
	}


	public function center($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('center')->where(['id'=>$key])->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('center'));
		}else{
			
			$info = Db::name('center')->order('sort asc')->paginate(10,false,['query'=>request()->param()]);

			$page = $info->render();

			$this->assign('info',$info);
			$this->assign('page',$page);

			// return $this->fetch();

			if($tab == 3){
				$infoEdit = Db::name('center')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}		
		return $this->fetch();
	}

	/*
	* PETMR - 新增数据
	*/
	public function addcenter(){
		if(request()->isPost()){
			$data = input('post.');
			$data['ip'] = request()->ip();
			$data['create_time'] = date('Y-m-d H:i:s',time());		

			$info = Db::name('center')->insert($data);
			if($info){
				return success('添加成功',url('center'));
			}else{
				return error('添加失败');
			}
		}
	}


	/*
	* PETMR - 修改数据
	*/
	public function editcenter(){
		if(request()->isPost()){
			$data = input('post.');
			$data['update_time'] = date('Y-m-d H:i:s',time());
			$id = input('post.id');
			$info = Db::name('center')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('center'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* PETMR - 删除数据
	*/
	public function deletecenter($id = 0){

		$info = Db::name('center')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('center'));
		}else{
			return error('删除失败');
		}
	}


	public function userinfo($id = 0, $tab = 2){
			
			$info = Db::name('userinfo')->paginate(10,false,['query'=>request()->param()]);


			$page = $info->render();

			$this->assign('info',$info);
			$this->assign('page',$page);

			// return $this->fetch();

			if($tab == 2){
				$infos = Db::name('userinfo')->where('id',$id)->find();
				if($infos){
					$this->assign('infos',$infos);
				}
			}	
		return $this->fetch();
	}


	//导出excel
   	 public function exportphpExcel(){

   
	        $list = Db::name('userinfo')->select();


	        vendor("PHPExcel.PHPExcel");
	        $objPHPExcel = new \PHPExcel();



	        $objPHPExcel->getProperties()->setCreator("ctos")
	            ->setLastModifiedBy("ctos")
	            ->setTitle("Office 2007 XLSX Test Document")
	            ->setSubject("Office 2007 XLSX Test Document")
	            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
	            ->setKeywords("office 2007 openxml php")
	            ->setCategory("Test result file");

	        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(50);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(50);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(50);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(50);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(50);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(50);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(50);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(50);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(50);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(50);

	        //设置行高度
	        // $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);

	        // $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);

	        //set font size bold
	        // $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
	        // $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setBold(true);
	        // $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);

	        // $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        // $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

	        //设置水平居中
	        // $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('I')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('J')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('K')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('L')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('M')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('O')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('P')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('Q')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('R')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('S')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('T')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('U')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        //合并cell
        	// $objPHPExcel->getActiveSheet()->mergeCells('A1:C1');

        	//长度不够显示的时候 是否自动换行
        	$objPHPExcel->getActiveSheet()->getStyle('L')->getAlignment()->setWrapText(true);
        	$objPHPExcel->getActiveSheet()->getStyle('M')->getAlignment()->setWrapText(true);
        	$objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setWrapText(true);
        	$objPHPExcel->getActiveSheet()->getStyle('O')->getAlignment()->setWrapText(true);
        	$objPHPExcel->getActiveSheet()->getStyle('P')->getAlignment()->setWrapText(true);
        	$objPHPExcel->getActiveSheet()->getStyle('Q')->getAlignment()->setWrapText(true);
        	$objPHPExcel->getActiveSheet()->getStyle('R')->getAlignment()->setWrapText(true);
        	$objPHPExcel->getActiveSheet()->getStyle('S')->getAlignment()->setWrapText(true);
        	$objPHPExcel->getActiveSheet()->getStyle('T')->getAlignment()->setWrapText(true);
        	$objPHPExcel->getActiveSheet()->getStyle('U')->getAlignment()->setWrapText(true);


	        // set table header content
	        $objPHPExcel->setActiveSheetIndex(0)
				        	// ->setCellValue('A1', '总计：'.$count.'条， 总和：'.$sum)
			            ->setCellValue('A1', '编号')
			            ->setCellValue('B1', '性名')
			            ->setCellValue('C1', '联系电话')
			            ->setCellValue('D1', '出生日期')
			            ->setCellValue('E1', '身高(cm)')
			            ->setCellValue('F1', '体重(kg)')
			            ->setCellValue('G1', '性别')
			            ->setCellValue('H1', '婚姻状况')
			            ->setCellValue('I1', '生育状况')
			            ->setCellValue('J1', '体检中心')
			            ->setCellValue('K1', '体检日期')
			            ->setCellValue('L1', '单位名称')
			            ->setCellValue('M1', '呼吸系统收集数据')
			            ->setCellValue('N1', '循环系统收集数据')
			            ->setCellValue('O1', '消化系统收集数据')
			            ->setCellValue('P1', '肝胆系统收集数据')
			            ->setCellValue('Q1', '泌尿系统收集数据')
			            ->setCellValue('R1', '骨髓造血系统收集数据')
			            ->setCellValue('S1', '内分泌系统收集数据')
			            ->setCellValue('T1', '神经系统收集数据')
			            ->setCellValue('U1', '骨关节运动系统收集数据');
        


	        // Miscellaneous glyphs, UTF-8
	        for($i=0;$i<count($list);$i++){
	            $objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $i+1);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $list[$i]['name']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $list[$i]['tel']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $list[$i]['dateSelectorOne_data_year'].'-'.$list[$i]['dateSelectorOne_data_month'].'-'.$list[$i]['dateSelectorOne_data_day']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $list[$i]['height']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $list[$i]['weight']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $list[$i]['sex']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $list[$i]['marry']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), $list[$i]['birth']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('J'.($i+2), $list[$i]['center']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('K'.($i+2), $list[$i]['dateSelectorTwo_data_year'].'-'.$list[$i]['dateSelectorTwo_data_month'].'-'.$list[$i]['dateSelectorTwo_data_day']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('L'.($i+2), $list[$i]['company']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('M'.($i+2), $list[$i]['message1'].','.$list[$i]['other1']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('N'.($i+2), $list[$i]['message2'].','.$list[$i]['other2']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('O'.($i+2), $list[$i]['message3'].','.$list[$i]['other3']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('P'.($i+2), $list[$i]['message4'].','.$list[$i]['other4']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('Q'.($i+2), $list[$i]['message5'].','.$list[$i]['other5']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('R'.($i+2), $list[$i]['message6'].','.$list[$i]['other6']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('S'.($i+2), $list[$i]['message7'].','.$list[$i]['other7']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('T'.($i+2), $list[$i]['message8'].','.$list[$i]['other8']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('U'.($i+2), $list[$i]['message9'].','.$list[$i]['other9']);
	            $objPHPExcel->getActiveSheet()->getRowDimension($i+2)->setRowHeight(16);
	        }

	        



        	$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
	       
	        //  sheet命名
	        $objPHPExcel->getActiveSheet()->setTitle('健康问卷调查');


	        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
	        $objPHPExcel->setActiveSheetIndex(0);


	        // excel头参数
	        header('Content-Type: application/vnd.ms-excel');
	        header('Content-Disposition: attachment;filename="健康问卷调查('.date('Y-m-d H-i-s').').xls"');  //日期为文件名后缀
	        header('Cache-Control: max-age=0');


	        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式

	        $objWriter->save('php://output');
    	
 
    }
}
?>