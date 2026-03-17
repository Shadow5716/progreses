fruitHTMLx();

function fruitHTMLx(){
	var obj = $(".fruitContent");
	var varHTML = "";
	
    for (fruit in fruits.fruits){
        //education.onlineCourse[course].title
        varHTML += "<hr/>";
        varHTML += "<div class='row'>";
        varHTML += "    <div class='col-md-8'>";
        varHTML += "         <a href='#' class='thumbnail'><img src='" + fruits.fruits[fruit].image + "' class='img-responsive' /></a>";
        varHTML += "    </div>";
        varHTML += "    <div class='col-md-4'>";
        varHTML += fruits.fruits[fruit].name + "<br/>";
        varHTML += fruits.fruits[fruit].color + "<br/>";
        varHTML += "        <input type='checkbox' name='chkF' value='"+ fruits.fruits[fruit].id +"' >";
        varHTML += "    </div>";
        varHTML += "</div>";
    }
    obj.append(varHTML);
}

            
vegetableHTMLx();

function vegetableHTMLx(){
    var obj = $(".vegetableContent");
    var varHTML = "";
    
    for (vegetable in vegetables.vegetables){
        //education.onlineCourse[course].title
        varHTML += "<hr/>";
        varHTML += "<div class='row'>";
        varHTML += "    <div class='col-md-8'>";
        varHTML += "         <a href='#' class='thumbnail'><img src='" + vegetables.vegetables[vegetable].image + "' class='img-responsive' /></a>";
        varHTML += "    </div>";
        varHTML += "    <div class='col-md-4'>";
        varHTML += vegetables.vegetables[vegetable].name + "<br/>";
        varHTML += vegetables.vegetables[vegetable].color + "<br/>";
        varHTML += "        <input type='checkbox' name='chkV' value='"+ vegetables.vegetables[vegetable].id +"' >";
        varHTML += "    </div>";
        varHTML += "</div>";
    }
    obj.append(varHTML);
}

cerealHTMLx();

function cerealHTMLx(){

    var obj = $(".cerealContent");
    var varHTML = "";
    
    for (cereal in cereales.cereales){
        //education.onlineCourse[course].title
        varHTML += "<hr/>";
        varHTML += "<div class='row'>";
        varHTML += "     <div class='col-md-8'>";
        varHTML += "         <a href='#' class='thumbnail'><img src='" + cereales.cereales[cereal].image + "' class='img-responsive' /></a>";
        varHTML += "    </div>";
        varHTML += "    <div class='col-md-4'>";
        varHTML += cereales.cereales[cereal].name + "<br/>";
        varHTML += cereales.cereales[cereal].color + "<br/>";
        varHTML += "        <input type='checkbox' name='chkC' value='"+ cereales.cereales[cereal].id +"' >";
        varHTML += "    </div>";
        varHTML += "</div>";
    }
    obj.append(varHTML);
}

semillaHTMLx();

function semillaHTMLx(){

    var obj = $(".semillaContent");
    var varHTML = "";
    
    for (semilla in semillas.semillas){
        //education.onlineCourse[course].title
        varHTML += "<hr/>";
        varHTML += "<div class='row'>";
        varHTML += "     <div class='col-md-8'>";
        varHTML += "         <a href='#' class='thumbnail'><img src='" + semillas.semillas[semilla].image + "' class='img-responsive' /></a>";
        varHTML += "    </div>";
        varHTML += "    <div class='col-md-4'>";
        varHTML += semillas.semillas[semilla].name + "<br/>";
        varHTML += semillas.semillas[semilla].color + "<br/>";
        varHTML += "        <input type='checkbox' name='chkS' value='"+ semillas.semillas[semilla].id +"' >";
        varHTML += "    </div>";
        varHTML += "</div>";
    }
    obj.append(varHTML);
}


leguminosaHTMLx();

 

function leguminosaHTMLx(){

    var obj = $(".leguminosaContent");
    var varHTML = "";
    
    for (leguminosa in leguminosas.leguminosas){
        //education.onlineCourse[course].title
        varHTML += "<hr/>";
        varHTML += "<div class='row'>";
        varHTML += "     <div class='col-md-8'>";
        varHTML += "         <a href='#' class='thumbnail'><img src='" + leguminosas.leguminosas[leguminosa].image + "' class='img-responsive' /></a>";
        varHTML += "    </div>";
        varHTML += "    <div class='col-md-4'>";
        varHTML += leguminosas.leguminosas[leguminosa].name + "<br/>";
        varHTML += leguminosas.leguminosas[leguminosa].color + "<br/>";
        varHTML += "        <input type='checkbox' name='chkL' value='"+ leguminosas.leguminosas[leguminosa].id +"'>";
        varHTML += "    </div>";
        varHTML += "</div>";
    }
    obj.append(varHTML);
}

