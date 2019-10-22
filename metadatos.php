<?php 
/*
* metadatos - a module template for Prestashop v1.5+
* Copyright (C) 2019 Alejandro Medina Jimenez.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('_PS_VERSION_'))
  exit;
 
class Metadatos extends Module
{
	// DB file
	const INSTALL_SQL_FILE = 'install.sql';

	public function __construct()
	{
		$this->name = 'metadatos';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'Alejandro Medina';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.7'); 
		// $this->dependencies = array('blockcart');

		parent::__construct();

		$this->displayName = $this->l('Metadatos');
		$this->description = $this->l('Añade metaetiquetas en el front');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}

	/**
 	 * install
	 */
	public function install()
	{
		// Create DB tables - uncomment below to use the install.sql for database manipulation
		/*
		if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return false;
		else if (!$sql = file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return false;
		$sql = str_replace(array('PREFIX_', 'ENGINE_TYPE'), array(_DB_PREFIX_, _MYSQL_ENGINE_), $sql);
		// Insert default template data
		$sql = str_replace('THE_FIRST_DEFAULT', serialize(array('width' => 1, 'height' => 1)), $sql);
		$sql = str_replace('FLY_IN_DEFAULT', serialize(array('width' => 1, 'height' => 1)), $sql);
		$sql = preg_split("/;\s*[\r\n]+/", trim($sql));

		foreach ($sql as $query)
			if (!Db::getInstance()->execute(trim($query)))
				return false;
		*/

		return parent::install() && $this->registerHook('displayMediHookMetadatos') && $this->registerHook('displayHeader') &&
      		$this->registerHook('displayBackOfficeHeader') && $this->registerHook('displayAdminHomeQuickLinks');
	}

	/**
 	 * uninstall
	 */
	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		return true;
	}

	/**
 	 * admin page
	 */	
	public function getContent()
	{
		return $this->display(__FILE__, 'views/templates/admin/metadatos.tpl');
	}

	// BACK OFFICE HOOKS

	/**
 	 * admin <head> Hook
	 */
	public function hookDisplayBackOfficeHeader()
	{
		// CSS
		$this->context->controller->addCSS($this->_path.'views/css/elusive-icons/elusive-webfont.css');
		// JS
		// $this->context->controller->addJS($this->_path.'views/js/js_file_name.js');	
	}

	/**
	 * Hook for back office dashboard
	 */
	public function hookDisplayAdminHomeQuickLinks()
	{	
		$this->context->smarty->assign('metadatos', $this->name);
	    return $this->display(__FILE__, 'views/templates/hooks/quick_links.tpl');    
	}

	// FRONT OFFICE HOOKS
	// https://www.ventadecolchones.com/toppers-viscoelasticos/topper-viscoelastico-dream-3-34.html?bt_product_attribute=761639#/83-medida-80x180
	/**
	* Footer After Product Thumbs Hook
	*/
	public function hookDisplayMediHookMetadatos($params){		 
		$id_product = (int) Tools::getValue('id_product');
		$attr = Tools::getValue('bt_product_attribute');
		$attr = explode("?", $attr)[0];
		$pricebase_f = 0;
		$pricebase = Db::getInstance()->executeS('Select price from ' . _DB_PREFIX_ . 'product Where id_product ='.$id_product.';');
		// echo 'HHH: '.$pricebase[0][0]; 
		//echo '<p>Select price from ' . _DB_PREFIX_ . 'product_attribute Where id_product ='.$id_product.' and id_product_attribute='.$attr.' ;</p>'; 
		foreach($pricebase as $ss){ 
			foreach($ss as $pp){ 
				$pricebase = $pp * 1.21;
			}
		}
		$pricebase_f = $pricebase;
		// echo 'Precio base : '.$pricebase_f;
		$priceattr = Db::getInstance()->executeS('Select price from ' . _DB_PREFIX_ . 'product_attribute Where id_product ='.$id_product.' and id_product_attribute='.$attr.' ;');
		////echo '<script>';
		////echo 'console.log("'.'Select price from ' . _DB_PREFIX_ . 'product_attribute Where id_product ='.$id_product.' and id_product_attribute='.$attr.'");';
		////echo '</script>';
		//echo '<p>'.$priceattr[0]['price'].'</p>';
		$priceattr_f = 0;
		// echo '<p>'.'Select price from ' . _DB_PREFIX_ . 'product_attribute Where id_product ='.$id_product.' and id_product_attribute='.$attr.' ;'.'</p>';
		if(count($priceattr)>=1 and is_array($priceattr)){
			//echo '<p>VC</p>';
			//echo '<p> Producto Estandar de Prestashop con Combinaciones</p>';
			foreach($priceattr as $rr){
				foreach($rr as $tt){
					$priceattr_f = $tt * 1.21;
					//echo '<p>VentadeColchones: '.$priceattr_f.'</p>';
				}
			}
		}else{
			$check_mp = Db::getInstance()->executeS('Select id_product from ' . _DB_PREFIX_ . 'megaproduct Where id_product ='.$id_product.';');
			
			//echo '<p>Consulta SQL:   '.'Select id_product from ' . _DB_PREFIX_ . 'megaproduct Where id_product ='.$id_product.';</p>';
			//echo '<p> Value: '.count($check_mp).'</p>';
			
			if(count($check_mp)>0){
				//echo '<p> Producto de Megaproduct</p>';
				
				$mpurl = Tools::getValue('mpurl');
				
				//echo '<p> Parametros URL: </p>';
				//echo $mpurl;
				//echo '<p> IDs Extraidos: </p>';
				
				$datos = explode('/', $mpurl);
				
				if(count($datos)>0){
					
					$n_attrmp = 0;
					$attrmp = array();
					
					foreach($datos as $d){
						//echo '<p>-- '.$d.'</p>';
						
						if(count(explode('-', $d))>2){
							$n_attrmp = array_push($attrmp, explode('-', $d)[2]);
						}						
					}
					
					if($id_product == 5268){
						$sumaattr = array();
						if(isset($attrmp[1]) && isset($attrmp[0])){
							$sumaattr = Db::getInstance()->executeS('Select price as p from ' . _DB_PREFIX_ . 'megaproductattributes Where id_product ='.$id_product.' and id_attribute = '.$attrmp[1].' and attributes = '.$attrmp[0].' and measure = "price" and limittype = 0;');
						
							$pricebase_f = 0;
						}
					
						//echo '<p>Select price as p from ' . _DB_PREFIX_ . 'megaproductattributes Where id_product ='.$id_product.' and id_attribute = '.$attrmp[1].' and attributes = '.$attrmp[0].' and measure = "price" and limittype = 0;</p>';
						//echo 'Select price as p from ' . _DB_PREFIX_ . 'megaproductattributes Where id_product ='.$id_product.' and id_attribute = '.$n_attrmp[1].' and attributes = '.$n_attrmp[0].' and measure = "price" and limittype = 0;';
						if (count($sumaattr) > 0){
							$priceattr_f = $sumaattr[0]['p'] * 1.21;
						}
					}else{ 
						//echo '<p> Datos recopilados de los IDs </p>';
						
						$sqltailmp = '';
						$cont = 0;
						foreach($attrmp as $a){
							//echo '<p>-- '.$a.'</p>';				
							$sqltailmp = $sqltailmp.' id_attribute = '.$a.' or';
							$cont = $cont + 1;
						}
						
						if($cont > 0){
							$sqltailmp = substr($sqltailmp, 0, strlen($sqltailmp)-2); 
							$sqltailmp = '('.$sqltailmp.')';
							//echo '<p>Resultado for : '.$sqltailmp.'</p>';
							
							//echo '<p>SQL: '.'Select SUM(price) as p from ' . _DB_PREFIX_ . 'megaproductattributes Where id_product ='.$id_product.' and '.$sqltailmp.' and measure = "price" and limittype = 0;'.'</p>';
						
							$sumaattr = Db::getInstance()->executeS('Select SUM(price) as p from ' . _DB_PREFIX_ . 'megaproductattributes Where id_product ='.$id_product.' and '.$sqltailmp.' and measure = "price" and limittype = 0;');
							
							//echo '<p>Conclusiones: '.$sumaattr[0]['p']*1.21.'</p>';
							if (count($sumaattr) > 0){
								$priceattr_f = $sumaattr[0]['p'] * 1.21;
							}
						}
					}
				}

			}else{
				//echo '<p> Producto Estandar de Prestashop sin Combinaciones</p>';
			}
		}
		
		
		$precio_final = $priceattr_f + $pricebase_f;
		$precio_final = round($precio_final, 2);
		
		
		echo '<script>';
		echo 'console.log("PRECIO C:  '.$precio_final.'");';
		echo '</script>';
		
		$this->smarty->assign('preciocombinacion', $precio_final);
		//$this->smarty->assign('link_products', $link_products);
	    return $this->display(__FILE__, 'views/templates/front/_display.tpl');  
	}
}

?>
