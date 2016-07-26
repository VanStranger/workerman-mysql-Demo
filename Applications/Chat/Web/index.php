<html><head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>workerman-chat PHP聊天室 Websocket(HTLM5/Flash)+PHP多进程socket实时推送技术</title>
  <script type="text/javascript">
  //WebSocket = null;
  </script>
  <link rel="stylesheet" href="./uploadify/uploadify.css">
  <link href="./css/bootstrap.min.css" rel="stylesheet">
  <link href="./css/style.css" rel="stylesheet">
  
  <script type="text/javascript" src="./js/jquery.min.js"></script>
  <script type="text/javascript" src="./uploadify/jquery.uploadify.min.js"></script>
  <script type="text/javascript">
    if (typeof console == "undefined") {    this.console = { log: function (msg) {  } };}
    WEB_SOCKET_SWF_LOCATION = "/swf/WebSocketMain.swf";
    WEB_SOCKET_DEBUG = true;
    function getArgs(){
        var args = {};
        var match = null;
        var search = decodeURIComponent("&"+location.search.substring(1));
        var reg = /(?:&([^&]+?)=([^&]+))/g;
        while((match = reg.exec(search))!==null){
            args[match[1]] = match[2];
        }
        return args;
    }
    var ws, name,username,userid, client_list={},urlget=getArgs();

    // 连接服务端
    function connect() {
       // 创建websocket
       ws = new WebSocket("ws://"+document.domain+":7272");
       // 当socket连接打开时，输入用户名
       ws.onopen = onopen;
       // 当有消息时根据消息类型显示不同信息
       ws.onmessage = onmessage; 
       ws.onclose = function() {
    	  console.log("连接关闭，定时重连");
          connect();
       };
       ws.onerror = function() {
     	  console.log("出现错误");
       };
    }

    // 连接建立时发送登录信息
    function onopen()
    {
        // if(!name)
        // {
        //     show_prompt();
        // }
        // 登录
        console.log(userid);
        var login_data = '{"type":"login","client_name":"'+username.replace(/"/g, '\\"')+'","userid":'+userid+',"room_id":"<?php echo isset($_GET['room_id']) ? $_GET['room_id'] : 1?>"}';
        console.log("websocket握手成功，发送登录数据:"+login_data);
        ws.send(login_data);
    }

    // 服务端发来消息时
    function onmessage(e)
    {
        console.log(e.data);
        var data = eval("("+e.data+")");
        switch(data['type']){
            // 服务端ping客户端
            case 'ping':
                ws.send('{"type":"pong"}');
                break;;
            // 登录 更新用户列表
            case 'login':
                //{"type":"login","client_id":xxx,"client_name":"xxx","client_list":"[...]","time":"xxx"}

                // say(data['client_id'],data['from_userid'], data['client_name'],  data['client_name']+' 加入了聊天室', data['time']);
                if(data['client_list'])
                {
                    client_list = data['client_list'];
                }
                else
                {
                    client_list[data['client_id']] = {"userid":data['from_userid'],"name":data['client_name']}; 
                }
                flush_client_list();
                console.log(data['client_name']+"登录成功");
                break;
            // 发言
            case 'say':
                //{"type":"say","from_client_id":xxx,"to_client_id":"all/client_id","content":"xxx","time":"xxx"}
                say(data['from_client_id'],data['from_userid'], data['from_client_name'], data['content'], data['time']);
                break;
            // 用户退出 更新用户列表
            case 'logout':
                //{"type":"logout","client_id":xxx,"time":"xxx"}

                // say(data['from_client_id'],data['from_userid'], data['from_client_name'], data['from_client_name']+' 退出了', data['time']);
                delete client_list[data['from_client_id']];
                flush_client_list();
        }
    }


    

    // 刷新用户列表框
    function flush_client_list(){
    	var userlist_window = $("#userlist");
    	var client_list_slelect = $("#client_list");
    	userlist_window.empty();
    	client_list_slelect.empty();
    	userlist_window.append('<h4>在线用户</h4><ul>');
    	client_list_slelect.append('<option value="all" id="cli_all">所有人</option>');
    	for(var p in client_list){
            userlist_window.append('<li id="'+p+'" userid="'+client_list[p].userid+'">'+client_list[p].name+'</li>');
            client_list_slelect.append('<option value="'+p+'" userid="'+client_list[p].userid+'">'+client_list[p].name+'</option>');
        }
    	$("#client_list").val(select_client_id);
    	userlist_window.append('</ul>');
    }

    // 发言
    function say(from_client_id,from_userid, from_client_name, content, time){
    	$("#dialog").append('<div class="speech_item"><img src="./img/'+from_userid+'.jpg" class="user_icon" /> '+from_client_name+' <br> '+time+'<div style="clear:both;"></div><div class="triangle-isosceles top">'+content+'</div> </div>');
    }
    function toconnect(){
      
      userid=urlget['userid'] || null;
      username=urlget['username'] || null;
      console.log(userid);
      console.log(username);
      if(userid){
          name=username;
          connect();
      }else{
        console.log("null");
        window.location.href="./login.html";
      }
    }
    $(function(){
    	select_client_id = 'all';
	    $("#client_list").change(function(){
	         select_client_id = $("#client_list option:selected").attr("value");
           select_userid=$("#client_list option:selected").attr("userid");
	    });
      // 提交对话
      $("#sub").click(function() {
        var input = document.getElementById("textarea");
        var to_client_id = $("#client_list option:selected").attr("value");
        var to_userid=$("#client_list option:selected").attr("userid");
        var to_client_name = $("#client_list option:selected").text();
        ws.send('{"type":"say","to_client_id":"'+to_client_id+'","to_userid":"'+to_userid+'","to_client_name":"'+to_client_name+'","content":"'+input.innerHTML.replace(/"/g, '\\"').replace(/\n/g,'\\n').replace(/\r/g, '\\r')+'"}');
        input.innerHTML = "";
        input.focus();
      });
      var time=Math.floor(new Date().getTime()/1000);
      $("#uploadify").uploadify({
        'swf':"./uploadify/uploadify.swf",
        'uploader':'./uploadify/uploadify.php',
        'buttonText':'图片',
        'height':30,
        'width':120,
        'fileTypeDesc':'Image Files',
        'fileTypeExts':'*.gif; *.jpg; *.png; *.psd',
        'auto':true,
        'multi':true,
        'onUploadSuccess':function(file,response,data){
          $("#textarea").html($("#textarea").html()+'<img src="./uploads/'+response+'">');
        }
        });
      $("#upmp3").uploadify({
        'swf':"./uploadify/uploadify.swf",
        'uploader':'./uploadify/uploadify1.php',
        'buttonText':'mp3',
        'height':30,
        'width':120,
        'fileTypeDesc':'audio Files',
        'fileTypeExts':'*.mp3; *.mp4; *.wav; *.wma',
        'auto':true,
        'multi':true,
        'onUploadSuccess':function(file,response,data){
          $("#textarea").html($("#textarea").html()+'<audio controls="controls" src="./uploads/'+response+'"></audio>');
        }
        });
    });
  </script>
</head>
<body onload="toconnect();">
    <div class="container">
	    <div class="row clearfix">
	        <div class="col-md-1 column">
	        </div>
	        <div class="col-md-6 column">
	           <div class="thumbnail">
	               <div class="caption" id="dialog"></div>
	           </div>
	          <!--  <form onsubmit="onSubmit(); return false;"> -->
	                 <select style="margin-bottom:8px" id="client_list">
                        <option value="all">所有人</option>
                    </select>
                    <input type="file" name="" id="uploadify">
                    <input type="file" name="" id="upmp3">
                  <!--   <textarea class="textarea thumbnail" id="textarea"></textarea> -->
                    <div id="textarea" contentEditable=true class="textarea thumbnail"></div>
                    <div class="say-btn"><input type="button" class="btn btn-default" id="sub" value="发表" /></div>
              <!--  </form> -->
               <div>
               &nbsp;&nbsp;&nbsp;&nbsp;<b>房间列表:</b>（当前在&nbsp;房间<?php echo isset($_GET['room_id'])&&intval($_GET['room_id'])>0 ? intval($_GET['room_id']):1; ?>）<br>
               &nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=1">房间1</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=2">房间2</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=3">房间3</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=4">房间4</a>
               <br><br>
               </div>
               <p class="cp">PHP多进程+Websocket(HTML5/Flash)+PHP Socket实时推送技术&nbsp;&nbsp;&nbsp;&nbsp;Powered by <a href="http://www.workerman.net/workerman-chat" target="_blank">workerman-chat</a></p>
	        </div>
	        <div class="col-md-3 column">
	           <div class="thumbnail">
                   <div class="caption" id="userlist"></div>
               </div>
               <a href="http://workerman.net:8383" target="_blank"><img style="width:252px;margin-left:5px;" src="./img/workerman-todpole.png"></a>
	        </div>
	    </div>
    </div>
 
</body>
</html>
