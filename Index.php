<html>
	<head>
		<link rel='stylesheet' type='text/css' href='style/look.css' > 
		<script src='js/jquery-1.10.2.js'></script>
		<script type='text/javascript' >
		
		$(document).ready( function () {
		var message;
		var messagebox= document.getElementById('chatty');
		var xm;
		var chata = document.getElementById('chatroom');
		var outbubble;
		var inbubble;
		messagebox.focus();
		});
		
		
		function toali() {
		messagebox = document.getElementById('chatty');
		message= messagebox.value;
		
		 if (message!=='')
		 {
		
		outbubble = document.createElement('div');
		outbubble.className="out message";
        outbubble.innerHTML=message;
		
		chatroom.appendChild(outbubble);
		
		xm = new XMLHttpRequest();
		
		xm.onreadystatechange = function() {
			if(xm.readyState == 4 && xm.status == 200) {
				inbubble = document.createElement('div');
				inbubble.className="in message";
				inbubble.innerHTML=xm.responseText;
				chatroom.appendChild(inbubble);
				$("html, body").animate({scrollTop:document.body.offsetHeight});
			}
		}
		xm.open("GET" , "ali.php?message=" + message , true);
		xm.send();
		//$('html,body').animate({ scrollTop: element.offset().top }, 'slow');
		//$(window).scrollTop('1000');
		$("html, body").animate({scrollTop:document.body.offsetHeight});
		messagebox.value="";
		messagebox.focus();
		}
	}
		
		</script>
	
	</head>


	<body>
		<div id='container'>
		
			<div id="chatroom">
														
				<div id='writingbox'>
					
						<input type='text'  id="chatty" onkeydown='Javascript: if (event.keyCode==13) toali()' autocomplete='off'>
						
						<input type='button' value='Send' id='send' OnClick='toali()'>
					
				</div>
				
			</div>
				
			    <div id='block'>
				
				</div>
					
		</div>
	</body>

</html>

