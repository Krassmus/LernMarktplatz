<?='<?xml version="1.0" encoding="UTF-8"?>'?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate><?= $currentDate ?></responseDate>
  <request verb=<?='"'.$verb.'"' ?>><?=htmlReady($request_url)?></request>

  <Identify>
    <repositoryName>Lernmaterialien vom StudIP-Lernmarktplatz - Unter angabe von folgenden Materialgruppen erhalten Sie frei zugängliche Materialien.</repositoryName>
      <baseURL><?= htmlReady("http://localhost/studip/plugins.php/lernmarktplatz/oai/")?></baseURL>
    
    <protocolVersion>2.0</protocolVersion>
    <adminEmail><?=htmlReady("studip@uni-osnabrueck.de")?></adminEmail>
    <earliestDatestamp><?= htmlReady($earliest_stamp)?></earliestDatestamp>
    <deletedRecord>no</deletedRecord>
    <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
    <compression>deflate</compression>
 </Identify>
</OAI-PMH>