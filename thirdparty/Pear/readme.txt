pear channel-discover pear.phing.info
pear install phing/phingdocs

pear install PhpDocumentor
pear install VersionControl_SVN 
pear install VersionControl_Git  
pear install PHP_CodeSniffer  
pear install Archive_Tar   
pear install Services_Amazon_S3   
pear install HTTP_Request2 

pear channel-discover pear.phpunit.de
pear remote-list -c phpunit
pear install phpunit/DbUnit
pear install phpunit/File_Iterator
pear install phpunit/FinderFacade
pear install phpunit/Object_Freezer
pear install phpunit/PHPUnit
pear install phpunit/phpcpd

pear channel-discover pear.phpmd.org
pear channel-discover pear.pdepend.org
pear install --alldeps phpmd/PHP_PMD

pear channel-discover pear.pdepend.org
pear install pdepend/PHP_Depend-beta

pear服务器
pear channel-discover pear.pirum-project.org
pear install pirum/Pirum

模板引擎
pear channel-discover pear.twig-project.org
pear install twig/Twig (or pear install twig/Twig-beta)
http://maggienelson.com/blog-archives/2009/05/orm-in-the-php-world
