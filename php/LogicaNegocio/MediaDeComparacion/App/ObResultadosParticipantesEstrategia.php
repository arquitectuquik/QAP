<?php
require_once __DIR__ . "/../Domain/ObtenedorResultadosInterface.php";
require_once __DIR__ . "/../../../repositorys/ResultadosRepository.php";
class ObResultadosParticipantesEstrategia implements ObtenedorResultadosInterface
{
    /**
     * Respositorio de resultado
     *
     * @var ResultadosRepository
     */
    private $resultadosRepository;

    private $idPrograma;
    private $idUnidad;
    private $idLote;
    private $idAnalito;

    private $fechaCorte;

    private $idConfigConsensoActual;
    public function __construct($idPrograma, $idUnidad, $idLote, $idAnalito, $fechaCorte, $idConfigConsensoActual = null)
    {
        $this->idPrograma = $idPrograma;
        $this->idUnidad = $idUnidad;
        $this->idLote = $idLote;
        $this->idAnalito = $idAnalito;
        $this->fechaCorte = $fechaCorte;
        $this->idConfigConsensoActual = $idConfigConsensoActual;


    }

    public function setRepository($repo)
    {
        $this->resultadosRepository = $repo;
    }

    public function getResultados()
    {
        return $this->resultadosRepository->todoLosParticipantesPorAnalito(
            $this->idPrograma,
            $this->idUnidad,
            $this->idLote,
            $this->idAnalito,
            $this->fechaCorte,
            $this->idConfigConsensoActual
        );
    }
}
