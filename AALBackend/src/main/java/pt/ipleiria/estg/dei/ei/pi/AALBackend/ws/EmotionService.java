package pt.ipleiria.estg.dei.ei.pi.AALBackend.ws;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.EmotionDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.EmotionBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.PersonBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Emotion;

import javax.annotation.security.RolesAllowed;
import javax.ejb.EJB;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import java.util.List;
import java.util.stream.Collectors;

@Path("emotions")
@Produces({MediaType.APPLICATION_JSON})
@Consumes({MediaType.APPLICATION_JSON})
@RolesAllowed({"Administrator", "Client"})
public class EmotionService {

    @EJB
    private EmotionBean emotionBean;
    @EJB
    private PersonBean personBean;

    @GET
    @Path("/")
    public Response getAllEmotionsWS(){
        return Response.status(Response.Status.OK)
                .entity(toDTOs(emotionBean.getAllEmotions()))
                .build();
    }

    @GET
    @Path("{name}")
    @RolesAllowed({"Administrator"})
    public Response getEmotionWS(@PathParam("name") String name) throws Exception{
        Emotion emotion = emotionBean.findEmotion(name);

        return Response.status(Response.Status.OK)
                .entity(toDTO(emotion))
                .build();
    }

    @GET
    @Path("groups/{group}")
    public Response getEmotionGroupWS(@PathParam("group") String group) throws Exception{
        return Response.status(Response.Status.OK)
                .entity(toDTOs(emotionBean.getAllEmotionsGroup(group)))
                .build();
    }

    @POST
    @Path("/")
    @RolesAllowed({"Administrator"})
    public Response createEmotionWS(EmotionDTO emotionDTO) throws Exception{
        String name = emotionBean.create(emotionDTO.getName(), emotionDTO.getGroup());
        Emotion emotion = emotionBean.findEmotion(name);

        return Response.status(Response.Status.OK)
                .entity(toDTO(emotion))
                .build();
    }

    @DELETE
    @Path("{name}")
    @RolesAllowed({"Administrator"})
    public Response deleteEmotionWS(@PathParam("name") String name) throws Exception{
        if(emotionBean.delete(name)){
            return Response.status(Response.Status.OK)
                    .entity("[Success] Emotion with name \'"+name+"\' was deleted")
                    .build();
        }
        return Response.status(Response.Status.INTERNAL_SERVER_ERROR)
                .build();
    }


    private EmotionDTO toDTO(Emotion emotion){
        return new EmotionDTO(
                emotion.getName(),
                emotion.getGroup()
        );
    }

    private List<EmotionDTO> toDTOs(List<Emotion> emotions){
        return emotions.stream().map(this::toDTO).collect(Collectors.toList());
    }
}
