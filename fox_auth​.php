<?php
// 代理配置【前端完全看不见，绝对安全】
$agent = [
    'user' => '750708031',
    'key'  => 'e4b23b967bb91642a9a2ded99dc0983f'
];

$msg = '';
$status = '';

// 1. 开通/续费授权
if (isset($_POST['act']) && $_POST['act'] === 'set') {
    $type = trim($_POST['type']);
    $time = trim($_POST['time']);
    $qq   = trim($_POST['qq']);
    $bot  = trim($_POST['bot']);

    if (!$qq) {
        $msg = '请输入主人QQ';
        $status = 'error';
    } elseif (!$bot) {
        $msg = '请输入机器QQ/设备码';
        $status = 'error';
    } else {
        $url = "https://admin.iqapi.cn/ajax.php?act=Api_SetAuth&" . http_build_query([
            'type' => $type,
            'time' => $time,
            'qq'   => $qq,
            'bot'  => $bot,
            'user' => $agent['user'],
            'key'  => $agent['key']
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($res, true);

        if ($data['code'] == 0) {
            $msg = '✅ ' . $data['msg'];
            $status = 'success';
        } else {
            $msg = '❌ ' . ($data['msg'] ?? '开通失败');
            $status = 'error';
        }
    }
}

// 2. 查询授权状态
$queryResult = '';
if (isset($_POST['act']) && $_POST['act'] === 'get') {
    $qq  = trim($_POST['q_qq']);
    $bot = trim($_POST['q_bot']);

    if (!$qq || !$bot) {
        $queryResult = '<div class="alert error">请填写主人QQ + 机器QQ/设备码</div>';
    } else {
        $url = "https://admin.iqapi.cn/ajax.php?act=Api_GetAuth&" . http_build_query([
            'qq'   => $qq,
            'bot'  => $bot,
            'user' => $agent['user'],
            'key'  => $agent['key']
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($res, true);

        if ($data['code'] == 0) {
            $queryResult = '<div class="alert success">✅ 查询成功<br/>';
            $queryResult .= "主人QQ：{$data['qq']}<br/>";
            $queryResult .= "机器QQ/设备：{$data['bot']}<br/>";
            $queryResult .= "授权类型：{$data['type']}<br/>";
            $queryResult .= "到期时间：{$data['expire']}<br/>";
            $queryResult .= "状态：{$data['status']}</div>";
        } else {
            $queryResult = '<div class="alert error">❌ ' . ($data['msg'] ?? '未查询到授权') . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fox群管 - 授权管理系统</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:system-ui,sans-serif}
        body{background:#f5f7fa;padding:20px;max-width:500px;margin:0 auto}
        .card{background:#fff;border-radius:12px;padding:25px;margin-bottom:20px;box-shadow:0 2px 12px rgba(0,0,0,0.08)}
        .title{text-align:center;color:#FF5722;margin-bottom:20px;font-size:22px;font-weight:bold}
        .tab{display:flex;margin-bottom:20px;border-radius:8px;overflow:hidden}
        .tab-item{flex:1;padding:12px;text-align:center;background:#eee;cursor:pointer}
        .tab-item.active{background:#FF5722;color:#fff}
        .tab-content{display:none}
        .tab-content.show{display:block}
        .form-item{margin-bottom:18px}
        .form-item label{display:block;margin-bottom:8px;color:#333;font-weight:500}
        .form-item input,.form-item select{width:100%;padding:12px 14px;border:1px solid #eee;border-radius:8px;font-size:15px;outline:none}
        .form-item input:focus,.form-item select:focus{border-color:#FF5722}
        .tip{font-size:12px;color:#999;margin-top:5px}
        .btn{width:100%;padding:14px;background:linear-gradient(45deg,#FF5722,#FF9800);color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:bold;cursor:pointer}
        .alert{padding:12px 15px;border-radius:8px;margin-bottom:15px;font-size:14px}
        .success{background:#e8f5e9;color:#2e7d32}
        .error{background:#fff1f0;color:#d32f2f}
    </style>
</head>
<body>
<div class="card">
    <div class="title">🦊 Fox群管授权管理</div>

    <?php if($msg):?>
    <div class="alert <?php echo $status?>"><?php echo $msg?></div>
    <?php endif?>

    <!-- 标签栏 -->
    <div class="tab">
        <div class="tab-item active" onclick="switchTab(0)">开通/续费</div>
        <div class="tab-item" onclick="switchTab(1)">查询授权</div>
    </div>

    <!-- 开通/续费 -->
    <div class="tab-content show" id="tab0">
        <form method="post">
            <input type="hidden" name="act" value="set">
            <div class="form-item">
                <label>授权类型</label>
                <select name="type" required>
                    <option value="1">1 - 群管单Q</option>
                    <option value="2">2 - 群管设备</option>
                    <option value="3">3 - 群管NT</option>
                </select>
                <div class="tip">类型2填设备码</div>
            </div>
            <div class="form-item">
                <label>授权时长</label>
                <select name="time" required>
                    <option value="1">1 - 月授权</option>
                    <option value="2">2 - 永久授权</option>
                </select>
            </div>
            <div class="form-item">
                <label>主人QQ</label>
                <input type="number" name="qq" placeholder="请输入主人QQ" required>
            </div>
            <div class="form-item">
                <label>机器QQ / 设备码</label>
                <input type="text" name="bot" placeholder="请输入机器QQ/设备码" required>
            </div>
            <button type="submit" class="btn">立即开通/续费</button>
        </form>
    </div>

    <!-- 查询授权 -->
    <div class="tab-content" id="tab1">
        <?php echo $queryResult?>
        <form method="post">
            <input type="hidden" name="act" value="get">
            <div class="form-item">
                <label>主人QQ</label>
                <input type="number" name="q_qq" placeholder="请输入主人QQ" required>
            </div>
            <div class="form-item">
                <label>机器QQ / 设备码</label>
                <input type="text" name="q_bot" placeholder="请输入机器QQ/设备码" required>
            </div>
            <button type="submit" class="btn">立即查询</button>
        </form>
    </div>
</div>

<script>
function switchTab(index){
    document.querySelectorAll('.tab-item').forEach((item,i)=>{
        item.classList.toggle('active',i===index)
        document.getElementById(`tab${i}`).classList.toggle('show',i===index)
    })
}
</script>
</body>
</html>
