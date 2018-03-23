<?php

    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'make_tags');
	
	$Bootstrap->usePackage('Tags');
	$TagContainer = new TagContainer();
	$TaggedObjectContainer = new TaggedObjectContainer();
	
	// This utility makes tags for all Shows and Artists in the Database
	
	// First, we'll do the Artists
	$ArtistContainer = new ArtistContainer();
	$Artists = $ArtistContainer->getAllArtists();
	if (is_array($Artists)){
	    foreach ($Artists as $Artist){
	        $Tag = new Tag();
	        $Tag->setParameter('TagText',$Artist->getParameter('ArtistFullName'));
	        echo "<p>".$Tag->getParameter('TagText')."</p>";
	        $TagContainer->addTag($Tag);
            $TaggedObjectContainer->addTagToObject($Tag,$Artist);
	    }
	}
	
	// Second, we'll do the Shows
	$ShowContainer = new ShowContainer();
	$Shows = $ShowContainer->getAllShows();
	if (is_array($Shows)){
	    foreach ($Shows as $Show){
	        $Tag = new Tag();
	        if ($Show->getParameter("ShowTitle") != $Show->getArtistNames()){
	            $Tag->setParameter('TagText',$Show->getParameter('ShowTitle')." (".$Show->getParameter('ShowYear').")");
	        }
	        else{
	            $Tag->setParameter('TagText',$Show->getParameter('ShowTitle')." in Concert (".$Show->getParameter('ShowYear').")");
	        }
	        echo "<p>".$Tag->getParameter('TagText')."</p>";
	        $TagContainer->addTag($Tag);
            $TaggedObjectContainer->addTagToObject($Tag,$Show);
	    }
	}

?>
