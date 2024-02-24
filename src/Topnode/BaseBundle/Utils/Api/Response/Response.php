<?php

namespace App\Topnode\BaseBundle\Utils\Api\Response;

use App\Topnode\BaseBundle\Utils\Api\Formatter\SimpleFormatter;
use App\Topnode\BaseBundle\Utils\Api\Http\StatusCode;
use App\Topnode\BaseBundle\Utils\Paginator\ViewPaginator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class Response
{
    /**
     *  @var \JMS\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var \JMS\Serializer\SerializationContext
     */
    private $serializationContext = null;

    public function __construct(
        SerializerInterface $serializer,
        TranslatorInterface $translator,
        ViewPaginator $paginator,
        SimpleFormatter $formatter
    ) {
        $this->translator = $translator;
        $this->serializer = $serializer;
        $this->paginator = $paginator;
        $this->formatter = $formatter;

        $this->setSerializationContext();
    }

    public function setSerializationContext(?array $groups = ['Default']): self
    {
        $this->serializationContext = SerializationContext::create()->setGroups($groups);

        return $this;
    }

    /**
     * Returns just an empty response.
     *
     * @var int The HTTP status code of the response
     *
     * @return Symfony\Component\HttpFoundation\JsonResponse
     */
    public function emptyResponse(int $status = 204, array $headers = []): JsonResponse
    {
        return $this->response($status, [], $headers);
    }

    /**
     * Returns an error generated from a symfony validator.
     *
     * @var int The HTTP status code of the response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function errorFromValidation(ConstraintViolationList $errors, string $message = '', int $status = 400)
    {
        $errorsFormatted = [];
        foreach ($errors as $error) {
            $errorsFormatted[] = $error->getMessage();
        }

        return $this->error($status, $message, $errorsFormatted);
    }

    /**
     * Returns an error generated from a validate form.
     *
     * @var int The HTTP status code of the response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function errorFromForm(FormErrorIterator $errors, string $message = '', int $status = 400)
    {
        $errorsFormatted = [];
        foreach ($errors as $error) {
            $cause = $error->getCause();

            if ($cause) {
                $propertyPath = $cause->getPropertyPath();

                if ('' === $cause->getRoot()->getName()) {
                    $propertyPath = str_replace(
                        ['children[', '].'],
                        ['', ''],
                        $propertyPath
                    );
                    $id = $propertyPath;
                } else {
                    $id = $cause->getRoot()->getName() . '_' . str_replace('data.', '', $propertyPath);
                }

                $errorsFormatted[] = [
                    'id' => $id,
                    'property_path' => $propertyPath,
                    'message' => $this->translator->trans($error->getMessage()),
                ];

                return $this->error($status, $message, $errorsFormatted);
            }

            $errorsFormatted[] = [
                'message' => $error->getMessage(),
            ];
        }

        return $this->error($status, $message, $errorsFormatted);
    }

    /**
     * Returns an error.
     *
     * @var int The HTTP status code of the response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function error(int $status = 500, string $message = '', array $details = [], array $headers = [])
    {
        if (0 == strlen($message)) {
            $message = StatusCode::getMessageForCode($status);
        }

        return $this->response($status, [
            'message' => $message,
            'errors' => $details,
        ], $headers);
    }

    /**
     * @param mixed|null $data Data to be serialized
     */
    public function response(?int $status = null, $data = null, ?array $headers = [])
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = $this->translator->trans($value);
                }
            }
        } elseif ($data instanceof \Doctrine\ORM\QueryBuilder) {
            $data = $this->formatter->format($this->paginator->paginate($data));
        }

        return new JsonResponse(
            $this->serializer->serialize($data, 'json', $this->serializationContext),
            ($status ?? StatusCode::OK),
            $headers,
            true
        );
    }
}
