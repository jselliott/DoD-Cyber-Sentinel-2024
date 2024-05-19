<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberQuest Character Builder</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
 <style>

        @import url(https://fonts.googleapis.com/css?family=Neucha|Cabin+Sketch&display=swap);

        body, html {
            height: 100%;
            margin: 0;
        }
        .gradient-background {
            background: linear-gradient(to bottom, #999, #333);
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: #888;
            padding: 20px;
            border-radius: 10px;
            box-shadow:2px 2px 5px #333;
            border: 1px solid #AAA;
            color: black;
        }

        #characters{
            width:300px;
        }

        .carousel-inner{
            border-radius:20px;
            border:3px solid #FFF;
        }

        .carousel-caption{
            border-radius:5px;
            background-color:rgba(0,0,0,0.5);
        }

        .text-muted {
            color:#AAA;
            font-style:italic;
        }

        .sr-only {
            display:none;
        }
        
    </style>
</head>
<body>

<div class="gradient-background">
    <div class="container">
    <div class="row">
        <h1>CyberQuest Character Builder</h1>
        <p>Use the fields below to build and save your character for the hottest new cyber tabletop RPG, coming to stores soon!</p>
    </div>
    <div class="row">
        <div class="col-sm">
            <div id="characters" class="carousel" data-ride="carousel" data-interval="false">
                <div class="carousel-inner">
                    <?php
                    $count = 0;
                    foreach($characters as $character){
                    ?>
                    <div class="carousel-item<?=($count == 0 ? ' active' : '')?>" data-charid="<?=$character->id?>">
                        <img class="d-block w-300" src="/assets/img/characters/<?=$character->image?>" alt="<?=$character->title?>">
                        <div class="carousel-caption d-none d-md-block">
                            <h5><?=$character->title?></h5>
                        </div>
                    </div>
                    <?php
                    $count++;
                    }
                    ?>
                </div>
                <a class="carousel-control-prev" href="#characters" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#characters" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
        </div>
        <div class="col-sm">
            <div class="form-group">
                <label for="charStr">Strength</label>
                <input type="number" min="0" max="20" class="form-control" id="charStr" value="0"/>
            </div>
            <div class="form-group">
                <label for="charDex">Dexterity</label>
                <input type="number" min="0" max="20" class="form-control" id="charDex" value="0"/>
            </div>
            <div class="form-group">
                <label for="charCon">Constitution</label>
                <input type="number" min="0" max="20" class="form-control" id="charCon" value="0"/>
            </div>
            <div class="form-group">
                <label for="charInt">Intelligence</label>
                <input type="number" min="0" max="20" class="form-control" id="charInt" value="0"/>
            </div>
            <div class="form-group">
                <label for="charWis">Wisdom</label>
                <input type="number" min="0" max="20" class="form-control" id="charWis" value="0"/>
            </div>
            <div class="form-group">
                <label for="charChr">Charisma</label>
                <input type="number" min="0" max="20" class="form-control" id="charChr" value="0"/>
            </div>
        </div>
        <div class="col-sm">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="charName" aria-describedby="nameHelp" placeholder="Character Name">
                    <small id="nameHelp" class="form-text text-muted">A cool name for your character.</small>
                </div>
                <div class="form-group">
                    <label for="charBio">Bio</label>
                    <textarea class="form-control" id="charBio" rows="3"></textarea>
                </div>
                <br><br>
                <div class="form-group">
                    <h5>Promo Code</h5>
                    <input type="text" class="form-control" id="promoCode" aria-describedby="promoCodeHelp" placeholder="Promo Code">
                    <small id="promoCodeHelp" class="form-text text-muted">Enter promotional codes here to unlock secret characters.</small>
                </div>
                <button type="submit" id="promo_button" class="btn btn-secondary">Submit</button>
        </div>
    </div>
    <div class="row" style="text-align:right; margin-top:20px;">
        <button type="submit" id="save_button" class="btn btn-primary bt-lg">Save</button>
    </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){

        var charID = 0;

        $('#characters').on('slide.bs.carousel', function (event) {
            charID = $(event.relatedTarget).data("charid");
        });

        $("#save_button").click(function(event){

            event.preventDefault();

            $("#save_button").attr("disabled","disabled");
            $("#save_button").addClass("disabled");

            const data = {
                charID: charID,
                charName: $("#charName").val(),
                charBio: $("#charBio").val(),
                charStr: $("#charStr").val(),
                charDex: $("#charDex").val(),
                charCon: $("#charCon").val(),
                charInt: $("#charInt").val(),
                charWis: $("#charWis").val(),
                charChr: $("#charChr").val()
            };

            fetch("/api/save", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                $("#save_button").removeAttr("disabled");
                $("#save_button").removeClass("disabled");
                if (!response.ok) {
                    throw new Error('An unknown error occurred.');
                }
                return response.json();
            })
            .then(data => {
                if(data.qr){
                    $("#modalLabel").html("Character Export")
                    $("#modal-body").html("<div style='text-align:center'>Scan this QR code using the CyberQuest Mobile App (Coming Soon) to save your character.</div><div><img src='"+data.qr+"'/></div>");
                    $("#modal").modal("show");
                } else {
                    console.error('Error:', data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });

        });

        $("#promo_button").click(function(event){

            event.preventDefault();

            $("#promo_button").attr("disabled","disabled");
            $("#promo_button").addClass("disabled");

            const data = {
                promo_code: $("#promoCode").val(),
            };

            fetch("/api/promo", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                $("#promo_button").removeAttr("disabled");
                $("#promo_button").removeClass("disabled");
                if (!response.ok) {
                    throw new Error('An unknown error occurred.');
                }
                return response.json();
            })
            .then(data => {
                if(data.unlocked){
                    $("#modalLabel").html("Character Unlocked!")
                    $("#modal-body").html("<div style='text-align:center'>You've unlocked the <strong>"+data.unlocked.title+"!</strong></div><div><img src='/assets/img/characters/"+data.unlocked.image+"' width='100%'/></div><div style='text-align:center'>"+data.flag+"</div>");
                    $("#modal").modal("show");
                } else {
                    $("#modalLabel").html("Error")
                    $("#modal-body").html("<div style='text-align:center'>"+data.error+"</div>");
                    $("#modal").modal("show");
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });

        });

    });
</script>
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel"></h5>
      </div>
      <div class="modal-body" id="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>
