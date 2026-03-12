
function clickaction(b){
	// Accion por defecto para Buttons;
	switch( b.id ){
	   case "Paraestatal":
		if (paraestatal1.style.display == 'block'){
			paraestatal1.style.display = 'none'; 
            paraestatal2.style.display = 'none';
			instituto1.style.display = 'none';
			instituto2.style.display = 'none';
			universidad1.style.display = 'none';
			universidad2.style.display = 'none';	
		   }else{		   
			paraestatal1.style.display = 'block'; 
            paraestatal2.style.display = 'block';
			instituto1.style.display = 'none';
			instituto2.style.display = 'none';
			universidad1.style.display = 'none';
			universidad2.style.display = 'none';	
		}			  
        break;
	   case "Instituto":
		if (instituto1.style.display == 'block'){
			paraestatal1.style.display = 'none'; 
            paraestatal2.style.display = 'none';
			instituto1.style.display = 'none';
			instituto2.style.display = 'none';
			universidad1.style.display = 'none';
			universidad2.style.display = 'none';
		   }else{		   
			paraestatal1.style.display = 'none'; 
            paraestatal2.style.display = 'none';
			instituto1.style.display = 'block';
			instituto2.style.display = 'block';
			universidad1.style.display = 'none';
			universidad2.style.display = 'none';	
		}	   
        break;
	   case "Universidad":
		if (universidad1.style.display == 'block'){
			paraestatal1.style.display = 'none'; 
            paraestatal2.style.display = 'none';
			instituto1.style.display = 'none';
			instituto2.style.display = 'none';
			universidad1.style.display = 'none';
			universidad2.style.display = 'none';
		   }else{		   
			paraestatal1.style.display = 'none'; 
            paraestatal2.style.display = 'none';
			instituto1.style.display = 'none';
			instituto2.style.display = 'none';
			universidad1.style.display = 'block';
			universidad2.style.display = 'block';	
		}	  
        break;						  
    }
}
