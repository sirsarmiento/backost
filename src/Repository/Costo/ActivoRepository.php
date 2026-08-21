<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Activo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\Entity\Empresa;
Use App\Entity\User;

/**
 * @method Activo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activo[]    findAll()
 * @method Activo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Activo::class);
    }

    /**
     * Create Activo.
     */
    public function post($data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Crear entidad principal - Activo
            $entity = $helper->setParametersToEntity(new Activo(), $data);

            // Convertir montos al formato correcto para la base de datos
            if (isset($data['costoInicial'])) {
                $entity->setCostoInicial($this->formatDecimalValue($data['costoInicial']));
            }

            if (isset($data['valorResidual'])) {
                $entity->setValorResidual($this->formatDecimalValue($data['valorResidual']));
            }

            if (isset($data['cantidad'])) {
                $entity->setCantidad($this->formatDecimalValue($data['cantidad']));
            }

            if (isset($data['valorUnitario'])) {
                $entity->setValorUnitario($this->formatDecimalValue($data['valorUnitario']));
            }
                
            // Validar entidad principal
            $errors = $validator->validate($entity);
            if ($errors->count() > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                
                return new JsonResponse([
                    'msg' => 'Errores de validación',
                    'errors' => $errorMessages
                ], 422);
            }
            
            // Obtener usuario actual
            $currentUser = $entityManager->getRepository(User::class)
                ->find($this->security->getUser()->getId());
            
            if (!$currentUser) {
                return new JsonResponse(['msg' => 'Usuario no encontrado'], 404);
            }
            
            $entity->setCreateBy($currentUser->getUserName());
            
            // Asignar empresa
            $empresa = $entityManager->getRepository(Empresa::class)
                ->find($this->security->getUser()->getIdempresa());
            
            if ($empresa) {
                $entity->setEmpresa($empresa);
            }

            // Persistir y flush
            $entityManager->persist($entity);
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Activo creado exitosamente',
                'id' => $entity->getId()
            ], 201);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convierte un valor con formato español (1.234,56) a formato numérico para BD (1234.56)
     */
    private function formatDecimalValue($value): string
    {
        // Si ya es numérico o null, retornar como string
        if ($value === null || $value === '') {
            return '0';
        }
        
        // Si ya es un número (sin comas)
        if (is_numeric($value)) {
            return (string) $value;
        }
        
        // Convertir de formato español a inglés
        // 1. Eliminar puntos (separadores de miles)
        $withoutThousandSeparator = str_replace('.', '', $value);
        // 2. Reemplazar coma por punto (separador decimal)
        $withDecimalPoint = str_replace(',', '.', $withoutThousandSeparator);
        
        // Validar que el resultado sea numérico
        if (!is_numeric($withDecimalPoint)) {
            throw new \InvalidArgumentException("Valor decimal inválido: {$value}");
        }
        
        return $withDecimalPoint;
    }

    public function getAll(): array 
    {
        try {
            $activos = $this->findAll();
            $result = [];

            foreach ($activos as $activo) {      
                $result[] = [
                    'id' => $activo->getId(),
                    'nombre' => $activo->getNombre(),
                    'costoInicial' => $activo->getCostoInicial(),
                    'valorResidual' => $activo->getValorResidual(),
                    'vidaUtil' => $activo->getVidaUtil(),
                    'fechaCompra' => $activo->getFechaCompra() ? $activo->getFechaCompra()->format('Y-m-d') : null,
                    'tipo' => $activo->getTipo(),
                    'cantidad' => $activo->getCantidad(),
                    'unidadMedida' => $activo->getUnidadMedida(),
                    'presentacion' => $activo->getPresentacion(),
                    'descripcion' => $activo->getDescripcion(),
                    'ubicacion' => $activo->getUbicacion(),
                    'valorUnitario' => $activo->getValorUnitario(),
                    // Propiedades faltantes agregadas:
                    'categoria' => $activo->getCategoria(),
                    'subCategoria' => $activo->getSubCategoria(),
                    'consumoMaquina' => $activo->getConsumoMaquina(),
                    'tarifa' => $activo->getTarifa(),
                    'costoMantenimiento' => $activo->getCostoMantenimiento(),
                    // Propiedades de auditoría:
                    'createAt' => $activo->getCreateAt() ? $activo->getCreateAt()->format('Y-m-d H:i:s') : null,
                    'createBy' => $activo->getCreateBy(),
                    'updateAt' => $activo->getUpdateAt() ? $activo->getUpdateAt()->format('Y-m-d H:i:s') : null,
                    'updateBy' => $activo->getUpdateBy(),
                    // Relación con Empresa
                    'empresa' => $activo->getEmpresa() ? [
                        'id' => $activo->getEmpresa()->getId(),
                        'nombre' => $activo->getEmpresa()->getNombre() // Ajusta según los campos de tu entidad Empresa
                    ] : null
                ];
            }

            return $result;
            
        } catch (\Exception $e) {
            // En caso de error, retornar un array vacío
            return [];
        }
    }

    /**
     * Update Activo.
     */
    public function update(int $id, $data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Buscar el Activo existente
            $activo = $this->find($id);
            
            if (!$activo) {
                return new JsonResponse(['msg' => 'Activo no encontrado'], 404);
            }

            // Actualizar entidad principal
            $activo = $helper->setParametersToEntity($activo, $data);

            // Convertir montos al formato correcto para la base de datos
            if (isset($data['costoInicial'])) {
                $activo->setCostoInicial($this->formatDecimalValue($data['costoInicial']));
            }

            if (isset($data['valorResidual'])) {
                $activo->setValorResidual($this->formatDecimalValue($data['valorResidual']));
            }

            if (isset($data['cantidad'])) {
                $activo->setCantidad($this->formatDecimalValue($data['cantidad']));
            }

            if (isset($data['valorUnitario'])) {
                $activo->setValorUnitario($this->formatDecimalValue($data['valorUnitario']));
            }
            
            // Validar entidad principal
            $errors = $validator->validate($activo);
            if ($errors->count() > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                
                return new JsonResponse([
                    'msg' => 'Errores de validación',
                    'errors' => $errorMessages
                ], 422);
            }

            // Obtener usuario actual para auditoría
            $currentUser = $entityManager->getRepository(User::class)
                ->find($this->security->getUser()->getId());

            if ($currentUser) {
                $activo->setUpdateBy($currentUser->getUserName());
                $activo->setUpdateAt(new \DateTime());
            }

            // Persistir y flush
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Registro actualizado exitosamente',
                'id' => $activo->getId()
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
