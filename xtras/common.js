function startClock() {
    setInterval(function () {
        $("#clock").text(new Date().toUTCString());
    }, 500); // update about every second
}

function around(base, delta) {
    var range = Math.ceil(2 * delta);
    return base - range / 2 + Math.floor(Math.random() * range);
}

function twoD(val) {
    return "" + ((val < 10) ? "0" : "") + val;
}


// user info
// msg colors
const MC_INFO = "#cf0";
const MC_ERROR = "#f00";
function informUser(msg, col = MC_INFO) {
    var $infoMsg = $("<div class='user-info'>" + msg + "</div>").css("background-color", col);
    $("body").append($infoMsg);
    setTimeout(function () { $infoMsg.remove(); }, 1500);
}


function searchStr(str, fct) {
    $.post("./dbio.php", {
        action: "search",
        "searched-str": str
    },
        (json) => {
            $("#search-results").children().remove();
            processSearchResults(json);
            $(".result-choice").click(fct)
        }
    );
}

function processSearchResults(json) {
    var d = JSON.parse(json);
    if (d.errMsg.length > 0) {
        informUser(d.errMsg);
    } else {
        var rows = d.data;
        for (i = 0; i < rows.length; i++) {
            var r = rows[i];
            $("#search-results").append("<div class='result-choice' id='choice-"+r.id+"'>"+r.name+" "+r.firstname+"</div>");
        }
        
    }
}
function initSearchField(fct) {
    $('#search-txt').on('keypress', function (e) {
        if (e.which == 13) {
            var t = $('#search-txt').val();
            if (t.length >= 0) {
                searchStr(t, fct);
            }
        }
    });
}

function deleteUser(id){
    if (confirm("Effacer fiche?")){
        $.post("./dbio.php", {
            action: "delete",
            "id": id
        },
            (json) => {
                console.log(json);
            }
        );
    }
}