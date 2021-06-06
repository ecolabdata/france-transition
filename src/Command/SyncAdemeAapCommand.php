<?php

namespace App\Command;

use App\Entity\Aid;
use App\Entity\Funder;
use App\Repository\AidRepository;
use App\Repository\FunderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SyncAdemeAapCommand extends Command
{
    protected static $defaultName = 'app:sync-ademe-aap';
    protected static $defaultDescription = 'Import data from API Dematiss';

    private AidRepository $aidRepository;
    private FunderRepository $funderRepository;
    private EntityManagerInterface $em;
    private HttpClientInterface $client;

    /**
     * SyncAdemeAapCommand constructor.
     * @param AidRepository $aidRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(
        AidRepository $aidRepository,
        EntityManagerInterface $em,
        HttpClientInterface $client,
        FunderRepository $funderRepository
    ){
        $this->aidRepository = $aidRepository;
        $this->funderRepository = $funderRepository;
        $this->em = $em;
        $this->client = $client;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->comment('Starting Dematiss AAP import.');
        $io->comment('Requesting data from API...');

        $response = $this->client->request(
            'GET',
            'https://appelsaprojets-bo.ademe.fr/api/appels'
        );

        if ($response->getStatusCode() !== 200) {
            $io->error('Dematiss returned an error');
            $io->error($response->getContent());
            return Command::FAILURE;
        }

        $io->comment('Done !');
        $io->comment('Iterating through result... !');

        $data = $response->toArray();

        foreach ($data as $aap) {
            $funder = $this->funderRepository->findOneBy(['name' => 'ADEME']);

            if (null === $funder) {
                $funder = new Funder();
                $funder->setName('ADEME');
                $funder->setWebsite('')
            }

            $aid = $this->aidRepository->findOneBy(['sourceId' => $aap['appel_id']]);

            // Seems like this is a new aid
            if (null === $aid) {
                $aid = new Aid();
                $aid->setSourceId($aap['appel_id']);
            }
            $aid
                ->setName($aap['donnees']['dossier_aap_titre'])
                ->setApplicationEndDate(new \DateTime(strtotime($aap['donnees']['dossier_aap_date_cloture'])))
                ->setState(Aid::STATE_DRAFT)
                ->setType(Aid::TYPE_AAP)
                ->setPerimeter($aap['donnees']['dossier_aap_couverture_geographique'])
                ->setAidDetails($aap['donnees']['dossier_aap_presentation'])
                ->setFundingSourceUrl($aap['donnees']['dossier_aap_URL_site_depot'])
                ->setGoal($aap['donnees']['dossier_aap_sous_titre'])
            ;
            dd($aid);
        }

        echo PHP_EOL;
//        $arg1 = $input->getArgument('arg1');
//
//        if ($arg1) {
//            $io->note(sprintf('You passed an argument: %s', $arg1));
//        }
//
//        if ($input->getOption('option1')) {
//            // ...
//        }

        $io->success('Database has been updated with latest dematiss data');

        return Command::SUCCESS;
    }
}
