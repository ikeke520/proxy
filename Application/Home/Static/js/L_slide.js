$(function(){
    var cur_i = 0;
	var silides_area = $("#bannerbox");
	var silides = $("#bannerbox ul li");
	var pre = $(".left");
	var next = $(".right");
	var btn_bottom = $("#bannerbox ol li");
	var len = silides.length;
	var timer;

	silides.eq(cur_i).addClass('cur');
	btn_bottom.eq(cur_i).addClass('cur');

	autoPlay();
	
	pre.click(function(){
		cur_i--;
		cur_i = (cur_i < 0)?len-1:cur_i;
		show();
	});
	next.click(function(){
		cur_i++;
		cur_i = (cur_i > len -1)?0:cur_i;
		show();
	});
	btn_bottom.each(function(i){
		$(this).click(function(){
			cur_i = i;
			show();
		});
	});
	silides_area.hover(function(){
		clearInterval(timer);
	},function(){
		autoPlay();
	});
	function autoPlay(){
		timer = setInterval(function(){
			cur_i++;
			if(cur_i > len-1){
				cur_i = 0;
			}
			show();
		},3000);
	}
	function show(){
		silides.eq(cur_i).addClass('cur').siblings().removeClass('cur');
		btn_bottom.eq(cur_i).addClass('cur').siblings().removeClass('cur');
	}
	
});
