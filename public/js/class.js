class Common{

   constructor(){}

   clear(obj){
      obj.innerHTML = "";
   }

   displayNoneById(idList = [], s="on"){
      for(var key in idList){
         var obj = document.getElementById(idList[key]);
         if(s == "on"){
            obj.style.display = "none";
         }else{
            obj.style.display = "";
         }
      }
   }

   displayNoneByClassName(classList = [], s = "on"){
      for(var key in classList){
         var objlist = document.getElementsByClassName(classList[key]);
         if(s == "on"){
            objlist[0].style.display = "none";
         }else{
            objlist[0].style.display = "";
         }
      }
   }

   displayNoneByClassNameV2(classList = [], s = "on", target = 0){
      for(var key in classList){
         var objlist = document.getElementsByClassName(classList[key]);
         console.log(objlist);
         if(target == "all"){
            for(var i = 0; i < objlist.length; i++){
               if(s == "on"){
                  objlist[i].style.display = "none";
               }else{
                  objlist[i].style.display = "";
               }
            }
         }else{

            if(s == "on"){
               objlist[target].style.display = "none";
            }else{
               objlist[target].style.display = "";
            }
         }

      }
   }

   getValueById(id){
      var value = document.getElementById(id).value;
      return value;
   }

   //select htmlを返す
   createSelectBox(list = [], idName = null, onChangeFuncName = null){

      var select = document.createElement("select");
      if(idName != ""){
         select.setAttribute("id", idName);
      }
      if(onChangeFuncName != ""){
         select.setAttribute("onchange", onChangeFuncName);
      }

      list.forEach(function(key, val){
         select.add( new Option( key, val ) );
      });
      
      return select.outerHTML;

   }

}
