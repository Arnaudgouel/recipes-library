<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Meilisearch\Bundle\Searchable;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ORM\Index(columns: ['title'])]
#[Searchable]
class Recipe
{

    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $servings = null;

    #[ORM\Column(nullable: true)]
    private ?int $prepMinutes = null;

    #[ORM\Column(nullable: true)]
    private ?int $cookMinutes = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, RecipeIngredient>
     */
    #[ORM\OneToMany(targetEntity: RecipeIngredient::class, mappedBy: 'recipe', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $recipeIngredients;

    /**
     * @var Collection<int, RecipeStep>
     */
    #[ORM\OneToMany(targetEntity: RecipeStep::class, mappedBy: 'recipe', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $recipeSteps;

    /**
     * @var Collection<int, CategoryRecipe>
     */
    #[ORM\ManyToMany(targetEntity: CategoryRecipe::class, inversedBy: 'recipes')]
    private Collection $category;

    public function __construct()
    {
        $this->recipeIngredients = new ArrayCollection();
        $this->recipeSteps = new ArrayCollection();
        $this->category = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['searchable'])]
    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getServings(): ?int
    {
        return $this->servings;
    }

    public function setServings(?int $servings): static
    {
        $this->servings = $servings;

        return $this;
    }

    public function getPrepMinutes(): ?int
    {
        return $this->prepMinutes;
    }

    public function setPrepMinutes(?int $prepMinutes): static
    {
        $this->prepMinutes = $prepMinutes;

        return $this;
    }

    public function getCookMinutes(): ?int
    {
        return $this->cookMinutes;
    }

    public function setCookMinutes(?int $cookMinutes): static
    {
        $this->cookMinutes = $cookMinutes;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, RecipeIngredient>
     */
    public function getRecipeIngredients(): Collection
    {
        return $this->recipeIngredients;
    }

    public function addRecipeIngredient(RecipeIngredient $recipeIngredient): static
    {
        if (!$this->recipeIngredients->contains($recipeIngredient)) {
            $this->recipeIngredients->add($recipeIngredient);
            $recipeIngredient->setRecipe($this);
        }

        return $this;
    }

    public function removeRecipeIngredient(RecipeIngredient $recipeIngredient): static
    {
        if ($this->recipeIngredients->removeElement($recipeIngredient)) {
            // set the owning side to null (unless already changed)
            if ($recipeIngredient->getRecipe() === $this) {
                $recipeIngredient->setRecipe(null);
            }
        }

        return $this;
    }

    /**
     * Get ingredient names for search indexing
     */
    #[Groups(['searchable'])]
    public function getIngredientNames(): array
    {
        $ingredientNames = [];
        foreach ($this->recipeIngredients as $recipeIngredient) {
            if ($recipeIngredient->getIngredient()) {
                $ingredientNames[] = $recipeIngredient->getIngredient()->getName();
            }
        }
        return $ingredientNames;
    }

    /**
     * @return Collection<int, RecipeStep>
     */
    public function getRecipeSteps(): Collection
    {
        return $this->recipeSteps;
    }

    public function addRecipeStep(RecipeStep $recipeStep): static
    {
        if (!$this->recipeSteps->contains($recipeStep)) {
            $this->recipeSteps->add($recipeStep);
            $recipeStep->setRecipe($this);
        }

        return $this;
    }

    public function removeRecipeStep(RecipeStep $recipeStep): static
    {
        if ($this->recipeSteps->removeElement($recipeStep)) {
            // set the owning side to null (unless already changed)
            if ($recipeStep->getRecipe() === $this) {
                $recipeStep->setRecipe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CategoryRecipe>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(CategoryRecipe $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
        }

        return $this;
    }

    public function removeCategory(CategoryRecipe $category): static
    {
        $this->category->removeElement($category);

        return $this;
    }
}
