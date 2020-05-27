
  
<?php
ini_set('memory_limit', '1024M');
include 'SpellCorrector.php';
header('Content-Type:text/html; charset=utf-8');
$limit = 10;
$div=false;
$correct = "";
$correct1="";
$output = "";
$query= isset($_REQUEST['q'])?$_REQUEST['q']:false;
$results = false;
if($query){
    require_once('/home/agjayasree/solr-php-client/Apache/Solr/Service.php');
    $choice = isset($_REQUEST['algorithm'])? $_REQUEST['algorithm'] : "lucene";
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/homework/');
    if(!$solr->ping()) {
            echo 'Solr service is not available';
    }
    if(get_magic_quotes_gpc() == 1){
        $query = stripslashes($query);
    }
    try{
        if(!isset($_GET['algorithm']))$_GET['algorithm']="lucene";
        if($_GET['algorithm'] == "lucene"){
            $param = array('sort'=>'');
            // $results = $solr->search($query, 0, $limit);
        }else{
            $param = array('sort'=>'pageRankFile desc');
            // $results = $solr->search($query, 0, $limit, $param);
        }

        $word = explode(" ",$query);
        $encode_query = str_replace(" ","+",$query);
        $spell = $word[sizeof($word)-1];
        for($i=0;$i<sizeOf($word);$i++){
          ini_set('memory_limit',-1);

          $che = SpellCorrector::correct($word[$i]);
          if($correct!="")
            $correct = $correct."+".trim($che);
          else{
            $correct = trim($che);
          }
            $correct1 = $correct1." ".trim($che);
        }
        $correct1 = str_replace("+"," ",$correct);
        $div=false;
        if(strtolower($query)==strtolower($correct1)){
          $results = $solr->search($query, 0, $limit, $param);
        }
        else {
          if(isset($_REQUEST['custom'])) {
            $results = $solr->search($correct, 0, $limit, $param);
            $div=false;
          }else{
            $div=true;
            $results = $solr->search($query, 0, $limit, $param);
          }
          $link = "http://localhost/index.php?q=$correct&algorithm=$choice&custom=true";
          $origin = "http://localhost/index.php?q=$encode_query&algorithm=$choice&custom=true";
          $output = "<div class='h3'>Did you mean: <a href='$link'>$correct1</a></div>"
                    ;

        }
    }
    catch(Exception $e){
        die("<html><head><title>SEARCH EXCEPTION</title></head><body><pre>{$e->__toString()}</pre></body></html>");
    }
}
?>
<html>
<head>
    <title> PHP Solr Client</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script> 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" rel="Stylesheet"></link>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="http://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
     
    <script>
        $(document).ready(function(){
          if(localStorage.selected) {
            $('#' + localStorage.selected ).attr('checked', true);
          }
          $('.inputabs').click(function(){
            localStorage.setItem("selected", this.id);
          });
        });
        $(function() {
              var URL_Start = "http://localhost:8983/solr/homework/suggest?q=";
              var URL_End = "&wt=json&indent=true";
              var count=0;
              var tags = [];
              $("#q").autocomplete({
                source : function(request, response) {
                  var correct="",before="";
                  var query = $("#q").val().toLowerCase();
                  var character_count = query.length - (query.match(/ /g) || []).length;
                  var space =  query.lastIndexOf(' ');
                  if(query.length-1>space && space!=-1){
                    correct=query.substr(space+1);
                    before = query.substr(0,space);
                  }
                  else{
                    correct=query.substr(0);
                  }
                  var URL = URL_Start + correct+ URL_End;
                  $.ajax({
                  url : URL,
                  success : function(data) {
                   var js =data.suggest.mySuggester;
                   var docs = JSON.stringify(js);
                   var jsonData = JSON.parse(docs);
                   var result =jsonData[correct].suggestions;
                   var j=0;
                   var stem =[];
                   console.log(result);
                   for(var i=0;i<5 && j<result.length;i++,j++){
                     for(var k=0;k<i && i>0;k++){
                       if(tags[k].indexOf(result[j].term) >=0){
                         i--;
                         continue;
                       }
                     }
                     if(result[j].term.indexOf('.')>=0 || result[j].term.indexOf('_')>=0)
                     {
                       i--;
                       continue;
                     }
                     var s =(result[j].term);
                     if(stem.length == 5)
                       break;
                     if(stem.indexOf(s) == -1)
                     {
                       stem.push(s);
                       if(before==""){
                         tags[i]=s;
                       }
                       else
                       {
                         tags[i] = before+" ";
                         tags[i]+=s;
                       }
                     }
                   }
                   console.log(tags);
                   response(tags);
                 },
                 dataType : 'jsonp',
                 jsonp : 'json.wrf'
               });
               },
               minLength : 1
})
           });
    </script>
</head>
<style>
	body{
 		background : -webkit-linear-gradient(left, #25c481, #25b7c4);
		background : linear-gradient(to right, #25c481, #25b7c4);
	}	
	th{
		padding-right : 10px;
	}
	td{
		padding-top : 5px;
	}
</style>
<body>
<div class="container d-flex justify-content-center">
  <h1 class="text-center">Solr Seach Page</h1><br/>
    <form class="text-center h4"  accept-charset="utf-8" method="get">
        <input  id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8');?>"
                list="searchresults"  autocomplete="off"/>
                <datalist id="searchresults"></datalist>
                <input type="hidden" name="spellcheck" id="spellcheck" value="false">
        <br/><?php if ($div){echo $output;}?><br/>
        <input class="inputabs" id="solr" type="radio" name="algorithm" value="lucene" /> Lucene
        <input class="inputabs" id="google" type="radio" name="algorithm" value="pagerank" /> PageRank <br/><br/>
        <input type="submit" />
    </form>
</div>
<?php

$count =0;
$prev="";
$arrayFromCSV =  array_map('str_getcsv', file('/home/agjayasree/Downloads/URLtoHTML_nytimes_news.csv'));
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
<div> Results <?php echo $start; ?> - <<?php echo $end;?> of <?php echo $total;?>:</div> 
<ol style="list-style:none;">
<?php

foreach ($results->response->docs as $doc){
	 foreach($doc as $field => $value){
                if($field == "og_url"){
                        $link = $value; 
        	}
	} 
?>

<li>
<a href = <?php echo $link ; ?>>
	<table style ="padding:10px;  ">
	<?php
		foreach($doc as $field => $value){
			if($field=="id"){ ?>
				<br><tr><th><?php echo "ID  "; ?></th>
				<td><?php echo htmlspecialchars($value,  ENT_NOQUOTES, 'utf-8') ; ?></td></tr>
			<?php } ?>
			<?php if($field=="title"){ ?>
				<tr><th><?php echo "Title  "; ?></th>
				<td><?php echo htmlspecialchars($value,  ENT_NOQUOTES, 'utf-8') ; ?></td></tr>
			<?php } ?>
			<?php if($field=="description"){ ?>
				<tr><th><?php echo "Description  "; ?></th>
				<td><?php echo htmlspecialchars($value,  ENT_NOQUOTES, 'utf-8') ; ?></td></tr>
			<?php } ?>
			<?php if($field=="og_url"){ ?>
				<tr><th><?php echo "URL  "; ?></th>
				<td><?php echo htmlspecialchars($value,  ENT_NOQUOTES, 'utf-8') ; ?></td></tr>
			<?php } ?>	
			
		
	<?php } ?>
	</table></a></li>
<?php
  }
echo "</ol></div>";
}
?>

</body>
</html>

