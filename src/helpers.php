<?php

if (! function_exists('ReloadGEnv')) {
    function ReloadGEnv($root_path)
    {
        $config_HOST = env("CONFIG_HOST");
        $config_file = env("CONFIG_FILE");
        if ($config_file && $config_file) {
            //请求java的公共配置中心
            \GouuseCore\Helpers\ConfigHelper::downConfig($root_path, $config_HOST, $config_file);
            return;
        }
        $config = new \GouuseCore\Rpcs\Config\Rpc();
        $conn = new \GouuseCore\Rpcs\ConnDriverGuzzle($config->getHost(), $config->getRpcFolder(), $config, 'EnvLib', 'getAllEnv');
        list ($data, $responseCode) = $conn->callRpc('EnvLib', 'getAllEnv', []);
        if (is_object($data)) {
            //抛异常了
            throw $data;
        }
        $data = \GouuseCore\Helpers\RpcHelper::decodeRpcData($config, $data);
        $globalFile = $root_path . 'config/globalEnv.php';
        $str = "<?php\n return [\n";
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $str .= "   '" . $key . "' => '" . $value . "',\n";
            }
        } else {
            throw new \Exception("config服务返回数据错误");
        }
        $str .= "];\n?>";
        if (!file_put_contents($globalFile, $str)) {
            throw new \Exception("请确保" . $globalFile . "文件可写");
        }
    }
}