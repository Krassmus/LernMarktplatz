<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
    http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
    <responseDate>2018-06-20T07:09:15Z</responseDate>
    <request verb="ListRecords" metadataPrefix="oai_lom-de" set="example">http://example.de/###??oai.php</request>
    <ListRecords>
        <record>
            <header>
                <identifier>BYTS#moodle-COurse-ID</identifier>
                <datestamp> @modified #2018-06-20T14:10:59Z</datestamp>
            </header>
            <metadata>

                <lom xmlns="http://ltsc.ieee.org/xsd/LOM" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ltsc.ieee.org/xsd/LOM
                    http://ltsc.ieee.org/xsd/lomv1.0/lom.xsd">
                    <general>
                        <identifier>
                            <catalog>example/catalog>
                                <entry>BYTS#moodle-COurse-ID#</entry>
                        </identifier>
                        <title>
                            <string language="de">#@titel#</string>
                        </title>
                        <language>de</language>
                        <description>
                            <string language="de">#@description#</string>
                        </description>
                        <keyword>
                            <string language="de">#@tags#</string>
                        </keyword>
                        <keyword>
                            <string language="de">#@tags#</string>
                        </keyword>
                    </general>

                    <lifeCycle>
                        <version>
                            <string language="de">1.0</string>
                        </version>
                        <status>
                            <source>LOMv1.0</source>
                            <value>final</value>
                        </status>
                        <contribute>
                            <role>
                                <source>LOMv1.0</source>
                                <value>Author</value>
                            </role>
                            <entity>
                                
                            </entity>
                            <date>
                                <dateTime>#@modified ?</dateTime>
                            </date>
                        </contribute>
                        <contribute>
                            <role>
                                <source>LOMv1.0</source>
                                <value>publisher</value>
                            </role>
                            <entity>
                                <![CDATA[BEGIN:vCard VERSION:3.0 FN:#BY-Mebis ## N:#Mebis#; END:vcard]]>
                            </entity>
                            <date>
                                <dateTime>@published 2017-01-01T00:00:00.0Z</dateTime>
                            </date>
                        </contribute>

                    </lifeCycle>

                    <technical>
                        <format>text/html</format>
                        <size>?</size>
                        <location>#http://xxxx.yyyy.de/file.txt#</location>
                        <duration>00:00:00</duration>
                    </technical>

                    <educational>
                        <learningResourceType>
                            <source>LREv3.0</source>
                            <value>#course</value>
                        </learningResourceType>

                        <intendedEndUserRole>
                            <source>LREv3.0</source>
                            <value>teacher</value>
                        </intendedEndUserRole>
                        <context>
                            <source>LREv3.0</source>
                            <value>compulsory education</value>
                        </context>
                    </educational>

                    <rights>
                        <copyrightAndOtherRestrictions>
                            <source>LOMv1.0</source>
                            <value>yes</value>
                        </copyrightAndOtherRestrictions>
                        <description>
                            <string language="xt-lic">#CC-by#CC-by-sa#CC-0#CC-by-sa-nd#</string>
                        </description>
                    </rights>

                    <classification>
                        <purpose>
                            <source>LOMv1.0</source>
                            <value>discipline</value>
                        </purpose>
                        <taxonPath>
                            <source>
                            <string language="x-t-eaf">BW-ZOERR-Fachgebiete</string>
                            </source>
                            <taxon>
                                <id>#020005#</id>
                                <entry>
                                    <string language="de">#Landespflege, Umweltgestaltung#siehe Wertebereich</string>
                                </entry>
                            </taxon>
                        </taxonPath>
                    </classification>
                </lom>
        </record>

        <resumptionToken expirationDate="2019-12-31T23:00:00Z" completeListSize="100" cursor="0">continue100-example</resumptionToken>
    </ListRecords>
</OAI-PMH>