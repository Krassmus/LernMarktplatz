<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate>2002-02-08T14:27:19Z</responseDate>
  <request verb=<?='"'.$verb.'"' ?> from=<?= '"'.$currentDate.'"' ?> 
    identifier=<?= '"'.$metadataPrefix.'"' ?> set=<?= '"'.$set.'"' ?>> 
    <?=htmlReady(Request::url()) ?> 
  </request>
  <ListMetadataFormats>
 
    <metadataFormat>
     <metadataPrefix>oai_lom-de</metadataPrefix>
     <schema>http://ltsc.ieee.org/xsd/LOM
       </schema>
     <metadataNamespace>http://www.w3.org/2001/XMLSchema-instance
       </metadataNamespace>
   </metadataFormat>
   
 </ListMetadataFormats>
</OAI-PMH>
