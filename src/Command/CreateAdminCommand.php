<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:admin:create',
    description: 'Crée un utilisateur administrateur avec un email et mot de passe personnalisés',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'administrateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe de l\'administrateur')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcer la création même si l\'utilisateur existe déjà')
            ->setHelp('Cette commande crée un utilisateur administrateur avec les rôles ROLE_ADMIN et ROLE_USER.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $force = $input->getOption('force');

        // Validation de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('L\'email fourni n\'est pas valide.');
            return Command::FAILURE;
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if ($existingUser && !$force) {
            $io->error(sprintf('Un utilisateur avec l\'email "%s" existe déjà. Utilisez l\'option --force pour le remplacer.', $email));
            return Command::FAILURE;
        }

        // Créer ou mettre à jour l'utilisateur
        if ($existingUser && $force) {
            $user = $existingUser;
            $io->note(sprintf('Mise à jour de l\'utilisateur existant avec l\'email "%s"', $email));
        } else {
            $user = new User();
            $user->setEmail($email);
            $io->note(sprintf('Création d\'un nouvel utilisateur avec l\'email "%s"', $email));
        }

        // Définir les rôles d'administrateur
        $user->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_USER']);

        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Sauvegarder en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            'Administrateur créé avec succès !' . PHP_EOL .
            'Email: %s' . PHP_EOL .
            'Rôles: %s',
            $user->getEmail(),
            implode(', ', $user->getRoles())
        ));

        return Command::SUCCESS;
    }
}
