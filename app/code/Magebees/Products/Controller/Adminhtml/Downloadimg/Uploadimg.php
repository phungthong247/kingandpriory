<?php
namespace Magebees\Products\Controller\Adminhtml\Downloadimg;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Uploadimg extends \Magento\Backend\App\Action
{
    public function execute()
    {
		$error = array();
		$formdata = $this->getRequest()->getPost()->toarray();
		$csvresult = array();
        $files =  $this->getRequest()->getFiles();
		
        if (isset($files['filename']['name']) && $files['filename']['name'] != '') {
			
			$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA);
			$media_dir = $mediaDirectory->getAbsolutePath('import');
			if (!is_dir($media_dir)) {
				mkdir($media_dir, 0777);
			}
			//$datetime = date('m-d-Y_h-i-s', time());
			//$export_file_name = "import_images_".$datetime.".csv";
			$export_file_name = "import_images.csv";
			$listheaader = ["store","websites","type","sku","image","small_image","thumbnail","gallery"];
		
			$varDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::VAR_DIR);
			$var_dir = $varDirectory->getAbsolutePath('import');
			if (!is_dir($var_dir)) {
				mkdir($var_dir, 0777);
			}
			
			if($formdata["flag"] == 1){
				$headerfiles = fopen($var_dir.'/'.$export_file_name, "w");
				fputcsv($headerfiles, $listheaader);
				fclose($headerfiles);
			}
			
            $filename = $files['filename']['tmp_name'];
			$handle = fopen($filename, 'r');
			$data = fgetcsv($handle, filesize($filename));
			if(isset($formdata['pointer_next']) && $formdata['pointer_next']!=1){
				$flag = false;
				fseek($handle,$formdata['pointer_next']);
			}else{
				$flag = true;
			}
			
			$count = 1; 
			$files = fopen($var_dir.'/'.$export_file_name, "a");			
            while ($data = fgetcsv($handle, filesize($filename))) {
				if($count > 5){
					break;
				}
				if (!empty($data)) {
				   try {
						$images =  explode("|", $data[4]);
						$mainimg = '';
						$galleryimg = '';
						for ($i=0; $i<count($images); $i++) {
							$URL = urldecode($images[$i]);
							$urlInfo = pathinfo($URL);
							$image_name = $urlInfo['basename'];
							$extension = $urlInfo['extension'];
							if (strtolower($extension) == 'jpg' || strtolower($extension) == 'png' || strtolower($extension) == 'gif' || strtolower($extension) == 'jpeg') {
								$img_list_filename = $media_dir . '/' . $image_name;
								if (file_exists($img_list_filename)) {
									$path_parts = pathinfo($image_name);
									$image_name = $path_parts['filename'].'_'.time().'.'.$path_parts['extension'];
								}
									
								$content = file_get_contents($images[$i]);
								$fps = fopen($media_dir."/".$image_name, "w");
								fwrite($fps, $content);
								fclose($fps);
								if ($i==0) {
									$mainimg .= "/".$image_name;
								} else {
									if ($i == (count($images)-1)) {
										$galleryimg .= "/".$image_name;
									} else {
										$galleryimg .= "/".$image_name."|";
									}
								}
							} else {
								$error[] = "File Type not Allowed";
							}
						}
						$listimg = [$data[0],$data[1],$data[2],$data[3],$mainimg,$mainimg,$mainimg,$galleryimg];
						fputcsv($files, $listimg);
					   
					   if ($count == 5) {
							$csvresult['count'] = $count;
							$csvresult['pointer_last'] = ftell($handle);
							$next = fgets($handle);
							if(!empty($next)){
								$csvresult['no_more'] = false;
							}else{
								$this->messageManager->addSuccess(__('Images are Downloaded Successfully.'));
								$csvresult['no_more'] = true;
							}
						
							$this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($csvresult));
							return;
						}
				   } catch (\Exception $e) {
						$error[] = $e->getMessage();
                   }
                }
			 	$count++;	
            }
			fclose($files);
			$csvresult['count'] = $count-1;
			$csvresult['pointer_last'] = ftell($handle);
			$csvresult['no_more'] = true;	
        }
        if (!empty($error)) {
			$productsLog = $this->_objectManager->create('Magebees\Products\Logger\Logger');
            $productsLog->info(print_r($error, true));
			$this->messageManager->addError(__('There are some issues while downloadable the images. Please check "var/log/magebeesproducts.log" file.'));
        }
		$this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($csvresult));
		return;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::downloadimg');
    }
}
