<?php
class CompareHashFiles {
    public $version = '3.6.2';
    public $remote_repozitory = 'https://github.com/joomla/joomla-cms/releases/download/%s/Joomla_%s-Stable-Full_Package.zip';
    public $changed = array();
    public $diff = array();

    public $JOOMLA_ROOT = null;

    private function error($message) {
        throw new Exception($message);
    }

    private function request($url, $post = false) {
        if (function_exists('curl_init')) {
            $context = null;
            if ($post) {
                $postdata = http_build_query($post);

                $opts = array('http' =>
                    array(
                        'method'  => 'POST',
                        'header'  => 'Content-type: application/x-www-form-urlencoded',
                        'content' => $postdata
                    )
                );
                $context  = stream_context_create($opts);
            }

            return file_get_contents($url, false, $context);
        }
        $cookie = dirname(__FILE__).'/cookie.txt';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url ); // отправляем на
        curl_setopt($ch, CURLOPT_HEADER, 0); // пустые заголовки
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // возвратить то что вернул сервер
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // следовать за редиректами
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);// таймаут4
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie); // сохранять куки в файл
        curl_setopt($ch, CURLOPT_COOKIEFILE,  $cookie);
        curl_setopt($ch, CURLOPT_POST, $post!==0 ); // использовать данные в post

        if($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $data = curl_exec($ch);
        curl_close($ch);
        unlink($cookie);
        return $data;
    }
    
    
    private function unzip($file, $hash_folder) {
        if (!class_exists('ZipArchive')) {
            return $this->error("Для продолжения работы необходим ZipArchive");
        }
        $zip = new ZipArchive;
        $res = $zip->open($file);
        if ($res === TRUE) {
            $zip->extractTo($hash_folder);
            $zip->close();
        } else {
            echo 'failed';
        }
    }

    private function readFolder($path, $callback, $callback_folder = false) {
        if (!file_exists($path)) {
            return;
        }

        $path = realpath($path) . $this->DS;
        $dir = opendir($path);
        while ($file = readdir($dir)) {
            if ($file !== '..' and $file !== '.') {
                if (is_dir($path . $file)) {
                    $this->readFolder($path . $file, $callback, $callback_folder);
                } else {
                    call_user_func($callback, $path . $file);
                }
            }
        }
        closedir($dir);

        if ($callback_folder) {
            call_user_func($callback_folder, $path);
        }
    }
    
    private function compare() {
        $this->readFolder($this->HASH_ROOT, function ($filepath) {
            $path = str_replace($this->HASH_ROOT, '', $filepath);
            if (file_exists($this->JOOMLA_ROOT . $path) and file_get_contents($this->JOOMLA_ROOT . $path) !== file_get_contents($filepath)) {
                // echo '<h2>' . $path . '</h2>';
                // echo Diff::toTable();
                $this->diff[$path] = Diff::compareFiles($filepath, $this->JOOMLA_ROOT . $path);
                $this->changed[] = $this->JOOMLA_ROOT . $path;
                flush();
            }
        });
    }

    public function getTempFile($relative = false) {
        static $temp = null;
        if (!$temp) {            
            $temp = $this->TMP . 'archive' . date('d.m.Y-H.i') . '.zip';
        }
        return !$relative ? $temp : str_replace(JPATH_ROOT . $this->DS, '', $temp);
    }
    private function getZipFile() {
        return $this->TMP . $this->version . '.zip';
    }

    private function clear() {
        $this->readFolder($this->HASH_ROOT, 'unlink', 'rmdir');
        if (file_exists($this->getZipFile())) {
            unlink($this->getZipFile());
        }
    }

    function __construct() {
        set_time_limit(0);
        $ds = $this->DS = DIRECTORY_SEPARATOR;

        $this->ROOT = dirname(__FILE__) . $ds;
        $this->TMP = dirname(__FILE__) . $ds . 'tmp' . $ds;
        $this->HASH_ROOT = $this->ROOT . 'hash' . $ds;

    }
    private function loadHash() {
        if (file_exists($this->HASH_ROOT)) {
            $this->clear();
        }

        mkdir($this->HASH_ROOT);
        
        $zipfile = $this->getZipFile();

        if (!file_exists($zipfile)) {
            $zipdata = $this->request(sprintf($this->remote_repozitory, $this->version, $this->version));
            if (strlen($zipdata) < 10000) {
                return $this->error('Файл архива версии, не существует. Попытка скачки, обернулась неудачей');                
            }
            file_put_contents($zipfile, $zipdata);
        }
        $this->unzip($zipfile, $this->HASH_ROOT);
    }

    function setJoomlaRoot($joomla_root) {
        $this->JOOMLA_ROOT = realpath($joomla_root) . $this->DS;
        return $this;
    }

    function setVersion($version) {
        if (!preg_match('#^[0-9]+\.[0-9]+\.[0-9]+$#', $version)) {
            return $this->error('Версия Joomla должна иметь формат: x.x.x');
        }
        $this->version  = $version;
        return $this;
    }

    function startCompare() {
        $this->loadHash();
        $this->compare();
        $this->clear();
        return $this;
    }

    function restore($zipfile) {
        $zipfile = $this->JOOMLA_ROOT . $zipfile;
        if (!file_exists($zipfile)) {
            return $this->error('Файл ' . $zipfile . ' не существует');
        }
        $zip = new ZipArchive();
        if (!$zip->open($zipfile)) {
            return $this->error('Не удалось открыть архив с измененными файлами');
        }

        $zip->extractTo($this->JOOMLA_ROOT);

        $zip->close();
        
        return $this;
    }

    function saveChanged() {
        if (!count($this->changed)) {
            return $this->error('Нет ни одного исправленного файла');
        }
        $tmp = $this->getTempFile();
        $zip = new ZipArchive();
        if (!$zip->open($tmp, ZIPARCHIVE::CREATE)) {
            return $this->error('Не удалось создать архив с измененными файлами');
        }
        foreach ($this->changed as $filepath) {
            $zip->addFromString(str_replace($this->JOOMLA_ROOT, '', $filepath), file_get_contents($filepath));
        }
        $zip->close();
        return $this;
    }
}

// $compare = new CompareHashFiles();
// include_once($compare->ROOT . 'lib' . $compare->DS . 'class.Diff.php');

// $compare
    // ->setVersion('3.6.2')
    // ->setJoomlaRoot(realpath(dirname(__FILE__) . $compare->DS . '..' . $compare->DS) . $compare->DS)
    // ->startCompare()
    // ->saveChanged();

