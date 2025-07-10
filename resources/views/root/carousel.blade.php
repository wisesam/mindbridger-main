<style>
	.carousel-showmanymoveone .carousel-control {
	  width: 4%;
	  background-image: none;
	}
	.carousel-showmanymoveone .carousel-control.left {
	  margin-left: 15px;
	}
	.carousel-showmanymoveone .carousel-control.right {
	  margin-right: 15px;
	}
	.carousel-showmanymoveone .cloneditem-1,
	.carousel-showmanymoveone .cloneditem-2,
	.carousel-showmanymoveone .cloneditem-3 {
	  display: none;
	}

	@media all and (min-width: 768px) {
	  .carousel-showmanymoveone .carousel-inner > .active.left,
	  .carousel-showmanymoveone .carousel-inner > .prev {
		left: -50%;
	  }
	  .carousel-showmanymoveone .carousel-inner > .active.right,
	  .carousel-showmanymoveone .carousel-inner > .next {
		left: 50%;
	  }
	  .carousel-showmanymoveone .carousel-inner > .left,
	  .carousel-showmanymoveone .carousel-inner > .prev.right,
	  .carousel-showmanymoveone .carousel-inner > .active {
		left: 0;
	  }
	  .carousel-showmanymoveone .carousel-inner .cloneditem-1 {
		display: block;
	  }
	}

	@media all and (min-width: 768px) and (transform-3d), all and (min-width: 768px) and (-webkit-transform-3d) {
	  .carousel-showmanymoveone .carousel-inner > .item.active.right,
	  .carousel-showmanymoveone .carousel-inner > .item.next {
		-webkit-transform: translate3d(50%, 0, 0);
				transform: translate3d(50%, 0, 0);
		left: 0;
	  }
	  .carousel-showmanymoveone .carousel-inner > .item.active.left,
	  .carousel-showmanymoveone .carousel-inner > .item.prev {
		-webkit-transform: translate3d(-50%, 0, 0);
				transform: translate3d(-50%, 0, 0);
		left: 0;
	  }
	  .carousel-showmanymoveone .carousel-inner > .item.left,
	  .carousel-showmanymoveone .carousel-inner > .item.prev.right,
	  .carousel-showmanymoveone .carousel-inner > .item.active {
		-webkit-transform: translate3d(0, 0, 0);
				transform: translate3d(0, 0, 0);
		left: 0;
	  }
	}

	@media all and (min-width: 992px) {
	  .carousel-showmanymoveone .carousel-inner > .active.left,
	  .carousel-showmanymoveone .carousel-inner > .prev {
		left: -25%;
	  }
	  .carousel-showmanymoveone .carousel-inner > .active.right,
	  .carousel-showmanymoveone .carousel-inner > .next {
		left: 25%;
	  }
	  .carousel-showmanymoveone .carousel-inner > .left,
	  .carousel-showmanymoveone .carousel-inner > .prev.right,
	  .carousel-showmanymoveone .carousel-inner > .active {
		left: 0;
	  }
	  .carousel-showmanymoveone .carousel-inner .cloneditem-2,
	  .carousel-showmanymoveone .carousel-inner .cloneditem-3 {
		display: block;
	  }
	}

	@media all and (min-width: 992px) and (transform-3d), all and (min-width: 992px) and (-webkit-transform-3d) {
	  .carousel-showmanymoveone .carousel-inner > .item.active.right,
	  .carousel-showmanymoveone .carousel-inner > .item.next {
		-webkit-transform: translate3d(25%, 0, 0);
				transform: translate3d(25%, 0, 0);
		left: 0;
	  }
	  .carousel-showmanymoveone .carousel-inner > .item.active.left,
	  .carousel-showmanymoveone .carousel-inner > .item.prev {
		-webkit-transform: translate3d(-25%, 0, 0);
				transform: translate3d(-25%, 0, 0);
		left: 0;
	  }
	  .carousel-showmanymoveone .carousel-inner > .item.left,
	  .carousel-showmanymoveone .carousel-inner > .item.prev.right,
	  .carousel-showmanymoveone .carousel-inner > .item.active {
		-webkit-transform: translate3d(0, 0, 0);
				transform: translate3d(0, 0, 0);
		left: 0;
	  }
	}
	
</style>
<div class="carousel carousel-showmanymoveone"  id="carousel123">
	<div class="carousel-inner">
    <?PHP
      if(isset($books)) {
        $cnt=1;
        foreach($books as $b){
			if($cnt++==1) $active_txt="active";
			else $active_txt=null;

			$bookUrl=config('app.url','/wlibrary')."/book/".$b->id;
			$imgUrl=config('app.url','/wlibrary')."/storage/cover_images/".$_SESSION['lib_inst']."/".$b->cover_image;
			$imgAlt=$b->title." ($b->author)";
			  
			$bookTxt=$b->title." ($b->author)"; // to be displayed below the book image if there is a cover image
			if(mb_strlen($bookTxt)>40) $bookTxt=mb_substr($bookTxt,0,38)."..";
			  
			if($b->cover_image) { // cover image exists 
				$cover_img_tag="<img src='$imgUrl' class='' width='200px' height='300px' alt=\"$imgAlt\">
				<span style='display:block;width:auto;'>$bookTxt</span>";
			}
			else { // no cover image
				$cover_img_tag="<div class='' style='max-width:200px;max-height:300px;border:solid #EEE 1px;'>$imgAlt</div>";
			}
			
			echo "<div class='item $active_txt img-fluid'>
					  <div class='col-xs-12 col-sm3-6 col-md3-3'><center>
						<a href='$bookUrl'>
						{$cover_img_tag}	
						</center></a></div>
				</div>";

        }
      }
    ?>
	
	</div>
	<a class="left carousel-control" href="#carousel123" data-slide="prev"><i class="glyphicon glyphicon-chevron-left"></i></a>
	<a class="right carousel-control" href="#carousel123" data-slide="next"><i class="glyphicon glyphicon-chevron-right"></i></a>
</div>

  <script>
  (function(){
  $('#carousel123').carousel({ interval: 3000 });
}());

(function(){
  $('.carousel-showmanymoveone .item').each(function(){
    var itemToClone = $(this);

    for (var i=1;i<4;i++) {
      itemToClone = itemToClone.next();

      // wrap around if at end of item collection
      if (!itemToClone.length) {
        itemToClone = $(this).siblings(':first');
      }

      // grab item, clone, add marker class, add to collection
      itemToClone.children(':first-child').clone()
        .addClass("cloneditem-"+(i))
        .appendTo($(this));
    }
  });
}());
</script>

 <script>
$(".carousel").on("touchstart", function(event){  // for swiping  
    var xClick = event.originalEvent.touches[0].pageX;
    $(this).one("touchmove", function(event){
        var xMove = event.originalEvent.touches[0].pageX;
        if( Math.floor(xClick - xMove) > 5 ){
            $(this).carousel('next');
        }
        else if( Math.floor(xClick - xMove) < -5 ){
            $(this).carousel('prev');
        }
    });
    
    $(".carousel").on("touchend", function(){
            $(this).off("touchmove");
    });
});
</script>