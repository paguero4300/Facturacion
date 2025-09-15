<div style="text-align: center; padding: 0; margin: 0;">
    @if($imageUrl)
        <img src="{{ $imageUrl }}" 
             alt="Imagen del producto" 
             style="max-width: 100%; max-height: 300px; width: auto; height: auto; object-fit: contain; border-radius: 4px;">
    @else
        <div style="padding: 20px; color: #666;">
            <p>No hay imagen disponible</p>
        </div>
    @endif
</div>