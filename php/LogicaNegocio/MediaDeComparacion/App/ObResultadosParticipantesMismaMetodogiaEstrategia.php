<?php
require_once __DIR__ . "/../Domain/ObtenedorResultadosInterface.php";
require_once __DIR__ . "/../../../repositorys/ResultadosRepository.php";
class ObResultadosParticipantesMismaMetodogiaEstrategia implements ObtenedorResultadosInterface
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

    private $idMetodologia;
    private $fechaCorte;
    private $idConfigConsensoActual;
    public function __construct($idPrograma, $idUnidad, $idLote, $idAnalito, $idMetodologia, $fechaCorte, $idConfigConsensoActual = null)
    {
        $this->idPrograma = $idPrograma;
        $this->idUnidad = $idUnidad;
        $this->idLote = $idLote;
        $this->idAnalito = $idAnalito;
        $this->idMetodologia = $idMetodologia;
        $this->fechaCorte = $fechaCorte;
        $this->idConfigConsensoActual = $idConfigConsensoActual;
    }


    public function setRepository($repo)
    {
        $this->resultadosRepository = $repo;
    }

    public function getResultados()
    {
        return $this->resultadosRepository->todosLosParticipantesMismaMetodologia(
            $this->idPrograma,
            $this->idUnidad,
            $this->idLote,
            $this->idAnalito,
            $this->idMetodologia,
            $this->fechaCorte,
            $this->idConfigConsensoActual
        );
    }
}