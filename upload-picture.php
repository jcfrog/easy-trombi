<?php
include("connect.php");
if (!$editmode){
    header("location:index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="xtras/jquery.min.js"></script>
    <title>Upload photo</title>
</head>

<body>
    <style>
        #container {
            width: 500px;
            margin: 0 auto;
        }

        canvas {
            border: 1px grey solid;
        }

        #commands {
            text-align: right;
        }

        .zoombut {
            width: 20px;
            height: 20px;
            background-size: contain;
            display: inline-block;
            cursor: pointer;
        }

        #zoomin {
            background-image: url(i/icons/zoomin.svg);
        }

        #zoomout {
            background-image: url(i/icons/zoomout.svg);
        }

        #validate {
            text-align: right;
        }

        .jcbut {
            padding: .5rem;
            border-radius: 3px;
            display: inline-block;
            margin-left: .5rem;
            background-color: #aaa;
            cursor: pointer;
        }

        #valid-but {
            background-color: cornflowerblue;
            color: white;
        }
        .helptxt{
            font-size: 12px;
            margin: 10px 0;
            color : gray;
        }
    </style>
    <div id="container">
        <h1>Chargement photo</h1>
        <div id="form">
            <input style="display:none;" type="file" name="pic-file" id="pic-file" accept="image/png, image/jpeg">
            <input type="button" id="loadFileBut" value="Importer image"
                onclick="document.getElementById('pic-file').click();" />
                
                
            </div>
            <div class="helptxt">Le copier/coller depuis le presse-papier est aussi possible (Ctrl-V).</div>
        <div id="commands">
            Zoom (%) : <input type="number" name="zoomval" id="zoomval" size=3>
            <div id="zoomin" class="zoombut"></div>
            <div id="zoomout" class="zoombut"></div>
        </div>
        <div id="image">
            <canvas id="cnv" width="500" height="500"></canvas>
        </div>
        <div id="validate">
            <div id="cancel-but" class="jcbut">Abandonner</div>
            <div id="valid-but" class="jcbut">Valider</div>
        </div>
    </div>
    <script>

        var selectArea = null;
        var bPanning = false;
        x1 = 0, y1 = 0, x2 = 0, y2 = 0;
        dragLastPos = null;
        var zoom = 1.0;
        var W = $("#cnv")[0].width;
        var H = $("#cnv")[0].height;
        var finalSize = 200;
        var offset = { x: 0, y: 0 };

        var img = null;
        var zoomedCnv = document.createElement('canvas');

        function rebuildZoomedImage() {
            if (img) {
                zoomedCnv.width = img.width * zoom;
                zoomedCnv.height = img.height * zoom;
                var ctx = zoomedCnv.getContext('2d');
                ctx.imageSmoothingQuality = "high";
                ctx.drawImage(img, 0, 0, img.width, img.height, 0, 0, img.width * zoom, img.height * zoom);
            }
        }

        function refreshDiplay() {
            if (img) {
                var ctx = $("#cnv")[0].getContext('2d');
                ctx.imageSmoothingQuality = "high";
                ctx.fillStyle = "#888";
                ctx.fillRect(0, 0, $("#cnv")[0].width, $("#cnv")[0].height);
                ctx.drawImage(zoomedCnv, offset.x, offset.y);

                // draw crop area
                ctx.strokeStyle = "#cf0";
                ctx.beginPath();
                ctx.moveTo((W - finalSize) / 2, (H - finalSize) / 2);
                ctx.lineTo((W + finalSize) / 2, (H - finalSize) / 2);
                ctx.lineTo((W + finalSize) / 2, (H + finalSize) / 2);
                ctx.lineTo((W - finalSize) / 2, (H + finalSize) / 2);
                ctx.lineTo((W - finalSize) / 2, (H - finalSize) / 2);
                ctx.closePath();
                ctx.stroke();

            }
        }

        function setZoomValue(z) {
            zoom = z;
            $("#zoomval").val(Math.round(z * 100));
        }
        
        var id = null;
        $(document).ready(() => {

            let params = new URLSearchParams(document.location.search);
            id = params.get("id");


            $("#pic-file").on("change", () => {
                const input = document.querySelector("input[type=file]");
                const [file] = input.files;
                if (file) {
                    var path = URL.createObjectURL(file);
                    img = new Image();
                    img.src = path;
                    img.onload = function () {
                        setZoomValue(Math.min(W / img.width, 1));
                        rebuildZoomedImage();
                        offset.x = (W - zoomedCnv.width) / 2;
                        offset.y = (H - zoomedCnv.height) / 2;
                        refreshDiplay();
                    }
                }
            });


            var startOffset = { x: 0, y: 0 };
            $("#cnv").on("mousedown", (e) => {
                if (img) {
                    bPanning = true;
                    x1 = e.clientX;
                    y1 = e.clientY;
                    startOffset.x = offset.x;
                    startOffset.y = offset.y;
                }
                refreshDiplay();
            });
            $("#cnv").on("mousemove", (e) => {
                if (img) {
                    if (bPanning) {
                        x2 = e.clientX;
                        y2 = e.clientY;
                        offset.x = startOffset.x + x2 - x1;
                        offset.y = startOffset.y + y2 - y1;
                        refreshDiplay();
                    }
                }
            });
            onmouseup = function (e) {
                bPanning = false;
            }

            function updateZoom(z) {
                if (img) {
                    // compute aiming point
                    // coordinates in zoomed canvas
                    var centerZ = { x: W / 2 - offset.x, y: H / 2 - offset.y };
                    // coordinates in original image
                    var centerI = { x: centerZ.x / zoom, y: centerZ.y / zoom };

                    setZoomValue(z);
                    rebuildZoomedImage();

                    // update offset to keep aiming point
                    offset.x = W / 2 - centerI.x * zoom;
                    offset.y = H / 2 - centerI.y * zoom;

                    refreshDiplay();
                }
            }


            $("#zoomin").click(() => {
                updateZoom(Math.min(2, zoom + 0.1));
            });

            $("#zoomout").click(() => {
                updateZoom(Math.max(0.1, zoom - 0.1));
            });


            // saving

            $("#valid-but").click(() => {
                if (img) {
                    var saveCnv = document.createElement('canvas');
                    saveCnv.width = finalSize;
                    saveCnv.height = finalSize;
                    var ctx = saveCnv.getContext("2d");
                    ctx.drawImage(zoomedCnv,
                        (W - finalSize) / 2 - offset.x, (H - finalSize) / 2 - offset.y, finalSize, finalSize,
                        0, 0, finalSize, finalSize);
                    
                    
                    // https://stackoverflow.com/questions/13198131/how-to-save-an-html5-canvas-as-an-image-on-a-server
                    var dataURL = saveCnv.toDataURL('image/jpeg', 0.85);
                    //console.log(dataURL);
                    $.ajax({
                        type: "POST",
                        url: "savepic.php",
                        data: {
                            imgBase64: dataURL,
                            id : id
                        }
                    }).done(function (o) {
                        console.log('saved',o);
                        window.location.href = "base.php?id="+id;
                        // If you want the file to be visible in the browser 
                        // - please modify the callback in javascript. All you
                        // need is to return the url to the file, you just saved 
                        // and than put the image in your browser.
                    });
                } else {
                    alert("Désolé, pas d'image chargée...");
                }
            });
            $("#cancel-but").click(() => {
                window.location.href = "base.php?id="+id;
            })

        });


        document.onpaste = function (pasteEvent) {
            // consider the first item (can be easily extended for multiple items)
            var item = pasteEvent.clipboardData.items[0];

            if (item.type.indexOf("image") === 0) {
                var blob = item.getAsFile();

                var reader = new FileReader();
                reader.onload = function (event) {
                    img = new Image();
                    img.src = event.target.result;
                    img.onload = function () {
                        setZoomValue(Math.min(W / img.width, 1));
                        rebuildZoomedImage();
                        offset.x = (W - zoomedCnv.width) / 2;
                        offset.y = (H - zoomedCnv.height) / 2;
                        refreshDiplay();
                    }
                };

                reader.readAsDataURL(blob);

                
            }
        }
    </script>
</body>

</html>