if (document.getElementById("whapz-puzzle")) {


    //---------------------------------//
    //   GLOBAL VARIABLES              //
    //---------------------------------//

    /* Start Optional vars */
    var whapz_horizontal_pieces = optional_puzzle_vars.whapz_horizontal_pieces;
    var whapz_vertical_pieces = optional_puzzle_vars.whapz_vertical_pieces;
    var whapz_scaling = optional_puzzle_vars.whapz_scaling;
    var whapz_canvas_color = optional_puzzle_vars.whapz_canvas_color;
    var whapz_opacity_img = optional_puzzle_vars.whapz_opacity_img;
    var whapz_control_panel_upload = optional_puzzle_vars.whapz_control_panel_upload;
    var whapz_post_id = optional_puzzle_vars.whapz_post_id;
    var whapz_bg_color = 'rgb(255, 255, 255, 0)';
	
	var $ = jQuery;


    //for mobile
    if ($('#whapz-puzzle').parent().width() <= '768') {
        var whapz_image = optional_puzzle_vars.whapz_mobile_image;
        var height = 400;
    }
    // for desktop
    else {
        var whapz_image = optional_puzzle_vars.whapz_desktop_image;
        var height = 768;

    }


    /* End Optional vars */

    // SCALING OPTIONS
    // scaling can have values as follows with full being the default
    // "fit"	sets canvas and stage to dimensions and scales to fit inside window size
    // "outside"	sets canvas and stage to dimensions and scales to fit outside window size
    // "full"	sets stage to window size with no scaling
    // "tagID"	add canvas to HTML tag of ID - set to dimensions if provided - no scaling

    // Global settings
    var scaling = whapz_scaling; // this will resize to fit inside the screen dimensions
    var countPieces = 0;
    var totalPieces = 0;
    var width = $('#' + scaling + '').width();


    if ($.cookie('whapz_full_screen') != null) {

        $('#wha-puzzle canvas').remove();

        $("html").animate({scrollTop: 0}, "fast");

        $('.whapz-small').fadeOut(300);

        if ($(window).width() > 768) {
            var whapz_image = optional_puzzle_vars.whapz_desktop_image;
        }

        whapz_frame('full', width, height);

        let whapz_cpu = '';
        if( whapz_control_panel_upload === '1' ){
            whapz_cpu =
                "<div class='whapz-form-upload'>\n"+
                "<form id='formElem' enctype='multipart/form-data'>" +
                "<input type='file' name='imagefile' id='imagefile' class='whapz-inputfile' />" +
                "<label for='imagefile'>Choose Image</label>" +
                "<input type='hidden' name='id_post' id='id_post' value='"+whapz_post_id+"'>" +
                "<input class='whapz-add-image' name='add-image' type='button' value='Add Image'>" +
                "</form></div>";
        }

        $('body').append("<div class='whapz-panel-puzzle whapz-full'>" +
            "<a id='whapz-button-full' title='Minimize screen' href='javascript:wha_screen(\"" + whapz_scaling + "\",flag=true);'>\n" +
            "<img src='/wp-content/plugins/wha-puzzle/images/icon-small-screen.png'></a>" +
            "<div class='whapz-timer-wrap'> \n" +
            "<div class='whapz-group-buttons'>\n" +
            "<input class='whapz-startButton' type='button' title='Start game'>\n" +
            "<input class='whapz-resetButton' type='button' title='Reset game'>\n" +
            "</div><div class='whapz-timer-item'>\n" +
            "<span  class='whapz-min'>00</span>:<span class='whapz-sec'>00</span></div></div>\n" +
            whapz_cpu +
            "<div class='whapz-completed'>Completed:<span></span></div>\n" +
            "</div></div>");

        $('.whapz-full').fadeIn(300);

    } else {
        wha_screen(scaling);
    }

    // Run app
    function wha_screen(scal, flag) {

        // Full screen
        if (scal == 'full') {
            $.cookie('whapz_full_screen', 'full');
            window.location.replace(window.location.pathname + window.location.search + window.location.hash);
        }

        // Normal screen
        if (flag) {
            $.cookie('whapz_full_screen', null); // delete cookie
            window.location.replace(window.location.pathname + window.location.search + window.location.hash);
        }

        whapz_frame(scal, width, height);
    }

    // Created puzzle
    function whapz_frame(scal, width, height) {

        // as of ZIM 5.5.0 you do not need to put zim before ZIM functions and classes
        var frame = new Frame(scal, width, height);


        frame.on("ready", function () {
            zog("ready from ZIM Frame"); // logs in console (F12 - choose console)

            var stage  = frame.stage;
            var stageW = frame.width;
            var stageH = frame.height;

            $('#myCanvas').css({'top': '-20px'});

            var puzzleX;
            var puzzleY;
            frame.outerColor = whapz_bg_color;
            frame.color = whapz_canvas_color;

            var con = new Container;

            // with chaining - can also assign to a variable for later access
            var imageObj = [];
            var piecesArrayObj = [];
            frame.loadAssets(["" + whapz_image + ""], "/wp-content/uploads/");

            var label = new Label({
                text: "",
                size: 50,
                font: "courier",
                color: "black",
                rollColor: "#3a3a3a",
                fontOptions: "bold"
            });
            stage.addChild(label);
            label.x = 20;
            label.y = 20;
            label.on("click", function () {
                zog("clicking");
            });

            frame.on("complete", function () {

                imageObj = frame.asset("" + whapz_image + "").clone();
                imageObj.addTo(con);
                imageObj.alpha = whapz_opacity_img;

                var piecesArray = new Array();
                var horizontalPieces = whapz_horizontal_pieces;
                var verticalPieces = whapz_vertical_pieces;
                var obj = getQueryString();
                zog(obj);
                if (obj) {
                    horizontalPieces = obj.row;
                    verticalPieces = obj.column;
                }
                var imageWidth = imageObj.width;
                var imageHeight = imageObj.height;
                var pieceWidth = Math.round(imageWidth / horizontalPieces);
                var pieceHeight = Math.round(imageHeight / verticalPieces);
                var gap = 20;
                totalPieces = horizontalPieces * verticalPieces;

                puzzleX = stageW / 2 - imageWidth / 2;
                puzzleY = stageH / 2 - imageHeight / 2;
                imageObj.pos(puzzleX, puzzleY);
                zog(puzzleX, puzzleY);

                //label.text = "Completed: "+countPieces+"/"+totalPieces;
                $('.whapz-completed span').text(countPieces + "/" + totalPieces);


                for (var j = 0; j < verticalPieces; j++) {

                    piecesArrayObj[j] = [];

                    for (var i = 0; i < horizontalPieces; i++) {
                        var n = j + i * verticalPieces;

                        var offsetX = pieceWidth * i;
                        var offsetY = pieceHeight * j;


                        var x8 = Math.round(pieceWidth / 8);
                        var y8 = Math.round(pieceHeight / 8);

                        piecesArrayObj[j][i] = new Object();
                        piecesArrayObj[j][i].right = Math.floor(Math.random() * 2);
                        piecesArrayObj[j][i].down = Math.floor(Math.random() * 2);

                        if (j > 0) {
                            piecesArrayObj[j][i].up = 1 - piecesArrayObj[j - 1][i].down;
                        }
                        if (i > 0) {
                            piecesArrayObj[j][i].left = 1 - piecesArrayObj[j][i - 1].right;
                        }

                        piecesArray[n] = new Rectangle({
                            width: pieceWidth,
                            height: pieceHeight,

                        });


                        var tileObj = piecesArrayObj[j][i];
                        var s = new Shape;

                        var context = s.graphics;
                        s.drag();
                        s.mouseChildren = false;
                        s.addEventListener("pressup", function (e) {
                            var mc = e.currentTarget;

                            var xx = Math.round(mc.x);
                            var yy = Math.round(mc.y);

                            //if (xx < puzzleX+gap / 2 && xx > puzzleX-gap / 2 && yy < puzzleX+gap / 2 && yy > puzzleY-gap / 2)

                            if (xx < puzzleX + gap / 2 && xx > puzzleX - gap / 2 && yy > puzzleY - gap / 2) {

                                if (!$('.whapz-startButton').hasAttr('data')) {
                                    WHAPZ_Clock.start();
                                    $('.whapz-startButton').attr('data', 'start').css({'background': 'url("/wp-content/plugins/wha-puzzle/images/icon-pause.png") no-repeat 0 0'});

                                }

                                mc.x = puzzleX;
                                mc.y = puzzleY;
                                mc.noDrag();
                                mc.addTo(mc.parent, 0);
                                mc.mouseChildren = false;
                                mc.mouseEnabled = false;
                                mc.hint.visible = false;
                                countPieces++;
                                //label.text = "Completed: "+countPieces+"/"+totalPieces;
                                $('.whapz-completed span').text(countPieces + "/" + totalPieces);

                                zog("countPieces", countPieces);

                                if (countPieces == totalPieces) {

                                    WHAPZ_Clock.pause();

                                    $('.whapz-startButton').removeAttr('data').css({'background': 'url("/wp-content/plugins/wha-puzzle/images/icon-start.png") no-repeat 0 0'});

                                    $('.whapz-used-minutes').text($('.whapz-min').eq(0).text());
                                    $('.whapz-used-second').text($('.whapz-sec').eq(0).text());


                                    $('.whapz_tw_share').attr("href", "https://twitter.com/share?text=" + encodeURIComponent('I did in ') + "" + $('.whapz-min').eq(0).text() + ":" + $('.whapz-sec').eq(0).text() + " " + encodeURIComponent('minutes, can you beat my record?') + "");
                                    $('.whapz_fb_share').attr("href", "https://www.facebook.com/share.php?u=http://"+window.location.hostname+"&t=" + encodeURIComponent('I did in ') + "" + $('.whapz-min').eq(0).text() + ":" + $('.whapz-sec').eq(0).text() + " " + encodeURIComponent('minutes, can you beat my record?') + "");
                                    $('.whapz_ln_share').attr("href", "http://www.linkedin.com/shareArticle?summary=" + encodeURIComponent('I did in ') + "" + $('.whapz-min').eq(0).text() + ":" + $('.whapz-sec').eq(0).text() + " " + encodeURIComponent('minutes, can you beat my record?') + "");


                                    $('#overlay').fadeIn(400,
                                        function () {
                                            $('#modal_form')
                                                .css('display', 'block')
                                                .animate({opacity: 1, top: '50%'}, 200);

                                            if( $('#modal_form .wrapper-inner iframe').length > 0 ) {

                                                const iframe = $('#modal_form .wrapper-inner iframe');
                                                iframe.css('height', '250px');
                                                iframe.css('width', '100%');

                                                if($(iframe[0]).context.height > 250 ) {
                                                    $('#modal_form .wrapper-inner').css({'overflowY':'scroll','maxHeight':'250px'});
                                                }
                                            }

                                            if ( $('#modal_form .wrapper-inner').height() > 250 && $('#modal_form .wrapper-inner iframe').length === 0) {

                                                $('#modal_form .wrapper-inner').css({'overflowY':'scroll','maxHeight':'250px'})
                                            }

                                        });

                                }
                                stage.update();
                            }
                        });
                        context.setStrokeStyle(3, "round");
                        var commandi1 = context.beginStroke(createjs.Graphics.getRGB(0, 0, 0)).command;
                        //
                        var commandi = context.beginBitmapFill(imageObj.image).command;


                        context.moveTo(offsetX, offsetY);


                        if (j != 0) {
                            context.lineTo(offsetX + 3 * x8, offsetY);
                            if (tileObj.up == 1) {
                                context.curveTo(offsetX + 2 * x8, offsetY - 2 * y8, offsetX + 4 * x8, offsetY - 2 * y8);
                                context.curveTo(offsetX + 6 * x8, offsetY - 2 * y8, offsetX + 5 * x8, offsetY);
                            } else {
                                context.curveTo(offsetX + 2 * x8, offsetY + 2 * y8, offsetX + 4 * x8, offsetY + 2 * y8);
                                context.curveTo(offsetX + 6 * x8, offsetY + 2 * y8, offsetX + 5 * x8, offsetY);
                            }
                        }
                        context.lineTo(offsetX + 8 * x8, offsetY);
                        if (i != horizontalPieces - 1) {
                            context.lineTo(offsetX + 8 * x8, offsetY + 3 * y8);
                            if (tileObj.right == 1) {
                                context.curveTo(offsetX + 10 * x8, offsetY + 2 * y8, offsetX + 10 * x8, offsetY + 4 * y8);
                                context.curveTo(offsetX + 10 * x8, offsetY + 6 * y8, offsetX + 8 * x8, offsetY + 5 * y8);
                            } else {
                                context.curveTo(offsetX + 6 * x8, offsetY + 2 * y8, offsetX + 6 * x8, offsetY + 4 * y8);
                                context.curveTo(offsetX + 6 * x8, offsetY + 6 * y8, offsetX + 8 * x8, offsetY + 5 * y8);
                            }
                        }
                        context.lineTo(offsetX + 8 * x8, offsetY + 8 * y8);
                        if (j != verticalPieces - 1) {
                            context.lineTo(offsetX + 5 * x8, offsetY + 8 * y8);
                            if (tileObj.down == 1) {
                                context.curveTo(offsetX + 6 * x8, offsetY + 10 * y8, offsetX + 4 * x8, offsetY + 10 * y8);
                                context.curveTo(offsetX + 2 * x8, offsetY + 10 * y8, offsetX + 3 * x8, offsetY + 8 * y8);
                            } else {
                                context.curveTo(offsetX + 6 * x8, offsetY + 6 * y8, offsetX + 4 * x8, offsetY + 6 * y8);
                                context.curveTo(offsetX + 2 * x8, offsetY + 6 * y8, offsetX + 3 * x8, offsetY + 8 * y8);
                            }
                        }
                        context.lineTo(offsetX, offsetY + 8 * y8);
                        if (i != 0) {
                            context.lineTo(offsetX, offsetY + 5 * y8);
                            if (tileObj.left == 1) {
                                context.curveTo(offsetX - 2 * x8, offsetY + 6 * y8, offsetX - 2 * x8, offsetY + 4 * y8);
                                context.curveTo(offsetX - 2 * x8, offsetY + 2 * y8, offsetX, offsetY + 3 * y8);
                            } else {
                                context.curveTo(offsetX + 2 * x8, offsetY + 6 * y8, offsetX + 2 * x8, offsetY + 4 * y8);
                                context.curveTo(offsetX + 2 * x8, offsetY + 2 * y8, offsetX, offsetY + 3 * y8);
                            }
                        }
                        context.lineTo(offsetX, offsetY);
                        s.addTo(con);

                        var fill = new createjs.Graphics.Fill("red");

                        //var newGra = context.append(fill);
                        var hint = new Shape();//s.clone(true);
                        hint.mouseChildren = false;
                        hint.mouseEnabled = false;
                        s.hint = hint;
                        hint.graphics = context.clone(true);
                        hint.pos(puzzleX, puzzleY);
                        // newGra.graphics = newGra;
                        hint.graphics._fill = fill;
                        hint.graphics._fill.style = null;

                        hint.addTo(con, 0);
                        //s.animate({obj:{x:frame.width-offsetX-pieceWidth,y:frame.height-offsetY-pieceHeight}, time:700});
                        //s.animate({obj:{x:-offsetX,y:-offsetY}, time:700});
                        s.animate({
                            obj: {
                                x: rand(-offsetX, stageW - offsetX - pieceWidth),
                                y: rand(-offsetY, stageH - offsetY - pieceHeight)
                            }, time: 700
                        });
                    }
                }


                con.addTo(stage);
                /*con.x -= imageWidth/2;
                con.y -= imageHeight/2;*/
                stage.update();


            }); // end asset complete


            stage.update(); // this is needed to show any changes

        }); // end of ready

    }

    // Close modal window ---
    $('#modal_close, #overlay').click(function () {

        var src = $('#modal_form iframe').attr('src');
        $('#modal_form iframe').attr('src', '');
        $('#modal_form iframe').attr('src', src);

        $('#modal_form')
            .animate({opacity: 0, top: '45%'}, 200,
                function () {
                    jQuery(this).css('display', 'none');
                    jQuery('#overlay').fadeOut(400);
                }
            );
    });

    // Init obj clock
    var WHAPZ_Clock = {

        totalSeconds: 0,

        start: function () {
            var self = this;

            function pad(val) {
                return val > 9 ? val : "0" + val;
            }

            this.interval = setInterval(function () {
                self.totalSeconds += 1;
                $(".whapz-min").text(pad(Math.floor(self.totalSeconds / 60 % 60)));
                $(".whapz-sec").text(pad(parseInt(self.totalSeconds % 60)));
            }, 1000);
        },
        reset: function () {
            WHAPZ_Clock.totalSeconds = null;
            clearInterval(this.interval);
            $(".whapz-min").text("00");
            $(".whapz-sec").text("00");
        },
        pause: function () {
            clearInterval(this.interval);
            delete this.interval;
        }
    };

    // Buttons panel
    $('.whapz-startButton').click(function () {

        if (!$(this).hasAttr('data')) {
            WHAPZ_Clock.start();
            $('.whapz-startButton').attr('data', 'start').css({'background': 'url("/wp-content/plugins/wha-puzzle/images/icon-pause.png") no-repeat 0 0'});

        } else {
            WHAPZ_Clock.pause();
            $('.whapz-startButton').removeAttr('data').css({'background': 'url("/wp-content/plugins/wha-puzzle/images/icon-start.png") no-repeat 0 0'});

        }
    });

    $('.whapz-resetButton').click(function () {
        window.location.replace(window.location.pathname + window.location.search + window.location.hash);
    });

    $.fn.hasAttr = function (name) {
        return this.attr(name) !== undefined;
    };


    /**
     * Upload Image on Frontend Page
     */

    window.onload = function() {

        // Add event Button
        $('.whapz-panel-puzzle .whapz-add-image').on('click',function(e){

            let btn = $(this);
            let label = $('.whapz-panel-puzzle form label');
            let file_data = $('.whapz-panel-puzzle #imagefile').prop('files')[0];
            let form = document.getElementById('formElem');
            let id_post = $('.whapz-panel-puzzle #id_post').val();

            let form_data = new FormData(form);

            if( undefined !== file_data && (
                file_data.type === 'image/png' ||
                file_data.type === 'image/jpg' ||
                file_data.type === 'image/gif' ||
                file_data.type === 'image/jpeg'))
            {

                form_data.append('file', file_data);
                form_data.append('id_post', id_post);
                form_data.append('action', 'frontend_image');

                let ajax_url ='/wp-admin/admin-ajax.php';

                $.ajax({
                    url: ajax_url,
                    type: 'post',
                    async: true,
                    cache: false,
                    contentType: false,
                    enctype: 'multipart/form-data',
                    processData: false,
                    data: form_data,
                    beforeSend: function() {
                        btn.attr({'value':'','disabled':'disabled'})
                        .css(
                            {'backgroundImage':'url("/wp-content/plugins/wha-puzzle/images/spinner.gif")',
                             'backgroundPosition':'center center',
                             'backgroundRepeat':'no-repeat',
                             'backgroundSize':'contain',
                             'width':'107px',
                             'cursor':'no-drop'
                            });
                    },
                    success: function (res) {
                        let result = JSON.parse(res);
                        if(result.status === 1){
                           window.location.replace(window.location.pathname + window.location.search + window.location.hash);
                        } else {
                            btn.attr({'value':'Error uploaded'}).css({'backgroundColor':'#f7a4a4'});
                        }
                    },
                    error: function (response) {
                        btn.attr({'value':'Error uploaded'}).css({'backgroundColor':'#f7a4a4'});
                        console.error(response);
                    }
                });
            } else {
                label.css({'backgroundColor':'#f7a4a4'}).text('Select Image');
            }

        });

    };

}


