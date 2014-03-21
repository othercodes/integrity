<?php
/**
 * Verifica si los archivos de un directorio han sido alterados.
 * @author David Unay Santisteban <slavepens@gmail.com>
 * @package SlaveFramework
 * @copyright (c) 2014, David Unay Santisteban
 * @license http://www.gnu.org/licenses/gpl.txt GPLv3 
 */
class integrity {
    
    protected $_path;
    protected $_tree = array();
    
    /**
     * Prepara la ruta para iniciar la comprobacion.
     * @param string $path
     */
    public function __construct($path) {
        if(substr($path,-1) != "/"){
           $this->_path = $path."/"; 
        } else {
            $this->_path = $path;
        }
        $this->getFileList();
    }
    
    /**
     * Verifica las firmas MD5 de los archivos y los cruza con los del archivo
     * dado para ver diferencias.
     * @param string $file
     * @return array 
     */
    public function checkMD5Hashes($file){
        if (!is_readable($file)) {
            return FALSE;
        }
        $file = file($file);
        foreach ($file as $line) {
            $temp = explode(' ', $line);
            $hashes[] = array('file' => $temp[0], 'md5' => trim($temp[1]));
        }
        $modifies = array();
        foreach($this->_tree as $currentHash){
            foreach($hashes as $storeHash){
                if($currentHash['file'] == $storeHash['file'] && $currentHash['md5'] != $storeHash['md5']){
                    $modifies[] = $this->getFileStats($currentHash['file']);
                }
            }
        }
        return $modifies; 
    }   
    
    /**
     * Genera un archivo con las firmas md5 de los archivos del 
     * directorio dado.
     * @param string $file
     * @return boolean
     */
    public function getMD5Hashes($file = null){
        if(!isset($file)){
            $file = date('YmdHis').".md5";
        }
        $hashes = '';
        foreach ($this->_tree as $value){
            $hashes .= $value['file']." ".$value['md5']."\n";
        }
        return file_put_contents($file, $hashes);
    }
    
    /**
     * Genera un array con la ruta, nombre y firma MD5 de cada archivo
     * de la ruta introducida.
     * @param string $path
     * @return boolean
     */
    private function getFileList($path = null){
        if(!$path){
            $path = $this->_path;
        }
        if(!is_dir($path)){
            return FALSE;
        }
        $root = opendir($path);
        while($entry = readdir($root)) {
            if ($entry != "." && $entry != "..") {
                if (is_dir($path.$entry)){
                    $this->getFileList($path.$entry."/");
                } else {
                    $this->_tree[] = array(
                        'file' => $path.$entry, 
                        'md5' => md5_file($path.$entry)
                    );
                }
            } 
        }
        closedir($root);
        return $this->_tree;
    }
    
    /**
     * Obtiene los metadatos de un archivo dado.
     * @param string $file
     * @return array
     */
    private function getFileStats($file){
        if(is_readable($file)) {
            $mdata = stat($file);
            return array(
                'filename' => $file,
                'md5Hash' => md5_file($file),
                'uid' => $mdata[4], 
                'gid' => $mdata[5], 
                'size' => $mdata[7],
                'lastAccess' => date('Y-m-d H:i:s',$mdata[8]),
                'lastModification' => date('Y-m-d H:i:s',$mdata[9]),
            ); 
        }
    }
}
