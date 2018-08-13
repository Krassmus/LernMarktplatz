<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate><?= $currentDate ?></responseDate>
  <request verb=<?='"'.$verb.'"' ?>><?=htmlReady(Request::url()) ?></request>

  <Identify>
    <repositoryName>Lernmaterialien vom StudIP-Lernmarktplatz - Unter angabe von folgenden Materialgruppen erhalten Sie frei zugängliche Materialien.</repositoryName>
    <? foreach ($identifier as $identity) : ?>
    <materialGroupId><?= $identity->name."-".$identity->tag_hash ?></identifier>
    <? endforeach ?>
    
    <baseURL>http://localhost/studip/plugins.php/lernmarktplatz/oai?verb=ListIdentifiers&metadataPrefix=oai_lom-de&set={materialGroupId}</baseURL>
    <protocolVersion>2.0</protocolVersion>
    <adminEmail>root@studip.de</adminEmail>
    <earliestDatestamp>1990-02-01T12:00:00Z</earliestDatestamp>
    <deletedRecord>transient</deletedRecord>
    <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
    <compression>deflate</compression>
    <description>
      <oai-identifier 
        xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation=
            "http://www.openarchives.org/OAI/2.0/oai-identifier
        http://www.openarchives.org/OAI/2.0/oai-identifier.xsd">
        <scheme>oai</scheme>
        <repositoryIdentifier>lcoa1.loc.gov</repositoryIdentifier>
        <delimiter>:</delimiter>
        <sampleIdentifier>oai:lcoa1.loc.gov:loc.music/musdi.002</sampleIdentifier>
      </oai-identifier>
    </description>
    <description>
      <eprints 
         xmlns="http://www.openarchives.org/OAI/1.1/eprints"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/1.1/eprints 
         http://www.openarchives.org/OAI/1.1/eprints.xsd">
        <content>
          <URL>http://memory.loc.gov/ammem/oamh/lcoa1_content.html</URL>
          <text>Selected collections from American Memory at the Library 
                of Congress</text>
        </content>
        <metadataPolicy/>
        <dataPolicy/>
      </eprints>
    </description>
    <description>
      <friends 
          xmlns="http://www.openarchives.org/OAI/2.0/friends/" 
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/friends/
         http://www.openarchives.org/OAI/2.0/friends.xsd">
       <baseURL>http://oai.east.org/foo/</baseURL>
     </friends>
   </description>
 </Identify>
</OAI-PMH>