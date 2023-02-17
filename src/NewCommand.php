<?php

namespace Laravel\Installer\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use GuzzleHttp\ClientInterface;
use ZipArchive;

class newCommand extends Command{


    private $client;
    
    public function __construct(ClientInterface $client){
        $this->client = $client;
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('new')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the application');
    }

        
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $directory = getcwd().'/'.$name;

        $this->verifyApplicationDoesntExist($directory,$output);

        $this->download($zipFile = $this->makeFileName())
            ->extract($zipFile,$directory)
            ->cleanUp($zipFile);

        return Command::SUCCESS;
    }


    public function verifyApplicationDoesntExist($directory, OutputInterface $output)
    {
        if (is_dir($directory)) {
            $output->writeln("<error>Application already exists!</error>");
            exit(1);
        }
    }

    private function download($zipFile)
    {
        $response = $this->client->request('GET','https://github.com/laravel/laravel/archive/refs/heads/10.x.zip')->getBody();

        file_put_contents($zipFile,$response);
        
        return $this;
    }

    private function extract($zipFile,$directory)
    {
        $archive = new ZipArchive();

        $archive->open($zipFile);

        $archive->extractTo($directory);
    
        $archive->close();

        return $this;
    }

    private function cleanUp($zipFile)
    {
        chmod($zipFile,0777);
        unlink($zipFile);

        return $this;
    }

    private function makeFileName()
    {
        return getcwd().'laravel'. md5(time().uniqid()).'.zip';
    }
}