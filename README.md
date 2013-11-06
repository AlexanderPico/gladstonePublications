gladstonePublications
=====================
usage:

<pre>php makePubTable.php</pre>

prerequisites:

* **curl**
 * on ubuntu: <pre>sudo apt-get install curl libcurl3 libcurl3-dev php5-curl</pre> then <pre>sudo apache2ct restart</pre>

key files:

* **makePubTable.php** - main script; takes results from other scripts and composes final CSV
* **bioXML2pubmedXML.php** - takes bio.xml, queries pubmed by name and Profiles by id, returns XML of pubmed ids
* **wosQuery.php** - takes XML of pubmed ids, queries isinet (thomson routers) for structured data, returns CSV table
* **bio.xml** - query set of investigator names and profile ids
* **out** - dir where CSV output is written
